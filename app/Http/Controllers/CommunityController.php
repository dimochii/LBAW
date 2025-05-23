<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\Notification;
use App\Models\RequestNotification;
use App\Models\CommunityNotification;
use App\Models\CommunityFollowRequest;
use App\Models\Image;
use App\Models\Report;
use Carbon\Carbon;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{

  public function createHub()
  {
    return view('pages.create_hub');
  }

  public function show($id)
  {
    if (!Auth::check()) {
      return redirect()->route('login');
    }

    if (Auth::user()->is_suspended) {
      return redirect()->route('news');
    }

    $community = Community::with(['posts', 'posts.authors', 'posts.votes', 'posts.comments'])
      ->findOrFail($id);

    if (!$community) {
      abort(404, 'Community not found');
    }

    $this->cacheRecentHub($community->id, $community->name);
    $sortOption = request()->query('sort', 'newest');

    $posts = $community->posts;

    if ($sortOption === 'newest') {
      $posts = $posts->sortByDesc('created_at');
    } elseif ($sortOption === 'top') {
      $posts = $posts->sortByDesc(function ($post) {
        return $post->votes->where('upvote', true)->count() - $post->votes->where('upvote', false)->count();
      });
    } elseif ($sortOption === 'trending') {
      $posts = $posts->sortByDesc(function ($post) {
        return $post->comments->count() + $post->votes->where('upvote', true)->count();
      });
    }

    $newsPosts = $posts->filter(function ($post) {
      return !is_null($post->news);
    });

    $topicPosts = $posts->filter(function ($post) {
      return !is_null($post->topic);
    });

    $posts_count = $posts->count();
    $followers_count = $community->followers()->count();

    $user = Auth::user();
    if ($user) {
      $is_following = $community->followers()
        ->where('authenticated_user_id', $user->id)
        ->exists();
    } else {
      $is_following = false;
    }

    $newPosts = $this->newPostsChart($id);
    $startDate = Carbon::now()->subDays(13)->toFormattedDateString();
    $endDate = Carbon::now()->toFormattedDateString();

    return view('pages.hub', [
      'community' => $community,
      'newsPosts' => $newsPosts,
      'topicPosts' => $topicPosts,
      'is_following' => $is_following,
      'posts_count' => $posts_count,
      'followers_count' => $followers_count,
      'newPosts' => $newPosts,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'sortOption' => $sortOption,
    ]);
  }


  private function cacheRecentHub($communityId, $communityName)
  {
    $userId = Auth::check() ? Auth::user()->id : null; // Check if the user is authenticated
    $cacheKey = $userId ? "recent_hubs:{$userId}" : "recent_hubs:guest";

    // Retrieve the community's image path
    $community = Community::with('image')->find($communityId);
    $imagePath = $community && $community->image ? $community->image->path : null;

    // Prepare the hub data including the image path
    $hubData = [
      'id' => $communityId,
      'name' => $communityName,
      'image' => $imagePath, // Add the image path
    ];

    // Fetch recent hubs from cache (use session for guests)
    $recentHubs = $userId
      ? Cache::get($cacheKey, [])
      : session()->get($cacheKey, []);

    // Remove the hub if it already exists
    $recentHubs = array_filter($recentHubs, fn($hub) => $hub['id'] !== $communityId);

    // Add the hub to the start
    array_unshift($recentHubs, $hubData);

    // Keep only the first 4 hubs
    $recentHubs = array_slice($recentHubs, 0, 4);

    if ($userId) {
      // Store in cache for authenticated users
      Cache::put($cacheKey, $recentHubs, now()->addHours(12));
    } else {
      // Store in session for guests
      session()->put($cacheKey, $recentHubs);
    }
  }



  public function updatePrivacy($id)
  {
    $community = Community::findOrFail($id);

    $this->authorize('updatePrivacy', $community);

    // Update the privacy status
    if ($community->privacy) {
      $community->privacy = false;
    } else {
      $community->privacy = true;
    }

    $community->save();

    // Return a JSON response
    return response()->json([
      'success' => true,
      'privacy' => $community->privacy ? 'Private' : 'Public',

    ]);
  }

  // Armazenar uma nova comunidade
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:communities',
      'description' => 'required|string|max:1000',
      'privacy' => 'required|in:public,private',
      'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
      'moderators' => 'nullable|array',
      'moderators.*' => 'exists:authenticated_users,id'
    ]);

    $image_id = null;
    if ($request->hasFile('image')) {
      $file = $request->file('image');
      $randomValue = uniqid();
      $extension = $file->extension();
      $filename = 'hub' . $randomValue . '.' . $extension;
      $file->move(base_path('images'), $filename);
      $image = Image::create([
        'path' => 'images/' . $filename
      ]);
      $image_id = $image->id;
    }

    $community = Community::create([
      'name' => $request->name,
      'description' => $request->description,
      'privacy' => $request->privacy === 'private',
      'image_id' => $image_id,
      'creation_date' => now(),
    ]);

    $community->moderators()->attach(Auth::user()->id);
    $community->followers()->attach(Auth::user()->id);

    if ($request->has('moderators')) {
      $community->moderators()->attach($request->moderators);
      $community->followers()->attach($request->moderators);
    }

    return redirect()->route('communities.show', ['id' => $community->id]);
  }





  public function join($id)
  {
    $community = Community::findOrFail($id);
    $user = auth()->user();

    if (!$community->privacy) {
      if (!auth()->user()->communities()->where('community_id', $id)->exists()) {
        auth()->user()->communities()->attach($id);
        return redirect()->back()->with('success', 'Successfully joined the community!');
      } else {
        return redirect()->back()->with('error', 'You are already following this community.');
      }
    }

    if (CommunityFollowRequest::where('community_id', $id)
      ->where('authenticated_user_id', auth()->user()->id)
      ->where('request_status', 'pending')
      ->exists()
    ) {
      return redirect()->back()->with('error', 'You have already requested to join this community.');
    }


    $request = CommunityFollowRequest::create([
      'authenticated_user_id' => $user->id,
      'community_id' => $id,
      'request_status' => 'pending',
      'request_date' => now(),
    ]);

    foreach ($community->moderators as $moderator) {
      $notification = Notification::create([
        'is_read' => false,
        'notification_date' => now(),
        'authenticated_user_id' => $moderator->id,
      ]);

      RequestNotification::create([
        'notification_id' => $notification->id,
        'request_id' => $request->id,
      ]);
    }

    return redirect()->back()->with('success', 'Your request was sent.');
  }



  public function leave($id)
  {
    $community = Community::findOrFail($id);
    if (auth()->user()->communities()->where('community_id', $id)->exists()) {
      auth()->user()->communities()->detach($id);
      return redirect()->back()->with('success', 'Successfully left the community!');
    }

    return redirect()->back()->with('error', 'You are not following this community.');
  }

  public function index(Request $request)
  {

    $sortBy = $request->get('sort_by', 'name');
    $order = $request->get('order', 'asc');

    $communities = Community::withCount('followers')
      ->orderBy($sortBy, $order)
      ->paginate(12);

    return view('pages.hubs', compact('communities', 'sortBy', 'order'));
  }

  public function getFollowers($id)
  {

    $community = Community::findOrFail($id);
    $user = Auth::user();
    if ($user) {
      $is_following = $community->followers()
        ->where('authenticated_user_id', $user->id)
        ->exists();
    } else {
      $is_following = false;
    }
    $followers = $community->followers()->get();

    return view('pages.hub_followers', [
      'community' => $community,
      'followers' => $followers,
      'is_following' => $is_following
    ]);
  }


  public function acceptFollowRequest($requestId)
  {
    $request = CommunityFollowRequest::findOrFail($requestId);

    $request->request_status = 'accepted';
    $request->save();

    $request->community->followers()->attach($request->authenticated_user_id);

    $notification = $request->notification;
    $notification->is_read = true;
    $notification->save();

    return response()->json(['success' => 'Follow request accepted.']);
  }


  public function rejectFollowRequest($requestId)
  {
    $request = CommunityFollowRequest::findOrFail($requestId);

    $request->request_status = 'rejected';
    $request->save();

    $notification = $request->notification;
    $notification->is_read = true;
    $notification->save();

    return response()->json(['success' => 'Follow request rejected.']);
  }

  public function deleteCommunity(Request $request, $id)
  {
    $admin = Auth::user();

    if (!$admin->is_admin) {
      return response()->json([
        'success' => false,
        'message' => 'You are not authorized to perform this action.'
      ], 403);
    }

    $community = Community::findOrFail($id);

    if ($community->posts()->exists()) {
      return response()->json([
        'success' => false,
        'message' => 'This community cannot be deleted as it contains posts.'
      ], 400);
    }

    try {
      $community->followers()->detach();
      $community->moderators()->detach();

      if ($community->followRequests()->exists()) {
        $community->followRequests()->delete();
      }

      if ($community->reports()->exists()) {
        $community->reports()->delete();
      }

      $community->delete();

      return response()->json([
        'success' => true,
        'message' => 'Community has been successfully deleted.'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'An error occurred while deleting the community.'
      ], 500);
    }
  }


  private function newPostsChart($id)
  {
    $startDate = now()->subDays(13)->startOfDay();
    $endDate = now()->endOfDay();

    // Fetch the data for news filtered by community ID
    $newsData = DB::table('news')
      ->join('posts', 'news.post_id', '=', 'posts.id')
      ->select(DB::raw('DATE(posts.creation_date) as post_date'), DB::raw('COUNT(*) as news_count'))
      ->where('posts.community_id', $id)
      ->whereBetween('posts.creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('news_count', 'post_date');

    // Fetch the data for topics filtered by community ID
    $topicsData = DB::table('topics')
      ->join('posts', 'topics.post_id', '=', 'posts.id')
      ->select(DB::raw('DATE(posts.creation_date) as post_date'), DB::raw('COUNT(*) as topics_count'))
      ->where('posts.community_id', $id)
      ->whereBetween('posts.creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('topics_count', 'post_date');

    // Fetch the data for all posts filtered by community ID
    $postsData = DB::table('posts')
      ->select(DB::raw('DATE(creation_date) as post_date'), DB::raw('COUNT(*) as total_count'))
      ->where('community_id', $id)
      ->whereBetween('creation_date', [$startDate, $endDate])
      ->groupBy('post_date')
      ->pluck('total_count', 'post_date');

    // Generate the labels (list of dates for the last 14 days)
    $labels = collect();
    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
      $labels->push($date->toDateString());
    }

    // Map the data to ensure each label has a corresponding value
    $newsCounts = $labels->map(fn($date) => $newsData[$date] ?? 0);
    $topicsCounts = $labels->map(fn($date) => $topicsData[$date] ?? 0);
    $postsCounts = $labels->map(fn($date) => $postsData[$date] ?? 0);

    // Build the combo chart
    $chart = Chartjs::build()
      ->name('newPostsChart')
      ->type('bar') // Base type for mixed charts
      ->size(['width' => 400, 'height' => 200])
      ->labels($labels->toArray())
      ->datasets([
        [
          "type" => "bar",
          "label" => "News",
          "backgroundColor" => "rgba(255, 99, 132, 0.5)",
          "borderColor" => "rgba(255, 99, 132, 1)",
          "data" => $newsCounts->toArray(),
        ],
        [
          "type" => "bar",
          "label" => "Topics",
          "backgroundColor" => "rgba(54, 162, 235, 0.5)",
          "borderColor" => "rgba(54, 162, 235, 1)",
          "data" => $topicsCounts->toArray(),
        ],
        [
          "type" => "line",
          "label" => "Total Posts",
          "backgroundColor" => "rgba(75, 192, 192, 0.5)",
          "borderColor" => "rgba(75, 192, 192, 1)",
          "fill" => false,
          "data" => $postsCounts->toArray(),
        ]
      ])
      ->options([
        "scales" => [
          "y" => [
            "beginAtZero" => true,
            "ticks" => [
              "stepSize" => 1, // Ensure integer-only y-axis
            ],
          ],
          "x" => [
            "type" => "time",
            "time" => [
              "unit" => "day",
            ],
          ],
        ],
      ]);

    return $chart;
  }
}
