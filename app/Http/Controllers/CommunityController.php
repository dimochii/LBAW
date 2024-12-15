<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
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


  public function destroy($id)
  {
    // Find the community by ID
    $community = Community::findOrFail($id);
    if (!($this->authorize('isAdmin') || $this->authorize('isCommunityAdmin', $community))) {
      abort(403, 'Unauthorized action.');
    }

    // Check if the community has any posts
    if ($community->posts()->exists()) {
      // If the community has posts, prevent deletion
      return redirect()->back()->with('error', 'Cannot delete a community that has posts.');
    }

    // If no posts exist, delete the community
    $community->delete();

    // Redirect back with a success message
    return redirect()->back()->with('success', 'deleted community.');
  }

  private function newPostsChart($id)
  {
    // Define the date range for the past 14 days
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

  public function show($id)
  {

    // Carregar comunidade com posts e autores (com o relacionamento correto)
    $community = Community::with(['posts.authors', 'posts.votes', 'posts.comments'])->find($id);

    // Verificar se a comunidade existe
    if (!$community) {
      abort(404, 'Community not found');
    }

    //dar cache à comunidade ao id the cache
    $this->cacheRecentHub($community->id, $community->name);

    // Mapeando os posts da comunidade
    $posts = $community->posts->map(function ($post) {
      // Contagem de upvotes e downvotes diretamente da tabela de votos
      $upvotes = $post->votes->where('upvote', true)->count();
      $downvotes = $post->votes->where('upvote', false)->count();

      return [
        'id' => $post->id,
        'title' => $post->title,
        'content' => $post->content,
        'authors_list' => $post->authors->pluck('name')->join(', '),
        'created_at' => $post->created_at,
        'score' => $upvotes - $downvotes,
        'comments_count' => $post->comments->count(),
        'news' => $post->news,  // Add the related news
        'topic' => $post->topic,
      ];
    });

    $newsPosts = $community->posts->filter(function ($post) {
      return !is_null($post['news']);
    });

    $topicPosts = $community->posts->filter(function ($post) {
      return !is_null($post['topic']);
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

    // moderation stats
    // $activeTab = request()->query('tab', 'news');
    $newPosts = $this->newPostsChart($id);
    $startDate = Carbon::now()->subDays(13)->toFormattedDateString();
    $endDate = Carbon::now()->toFormattedDateString();

    return view('pages.hub', [
      'community' => $community,
      // 'posts' => $posts,
      'newsPosts' => $newsPosts,
      'topicPosts' => $topicPosts,
      'is_following' => $is_following,
      'posts_count' => $posts_count,
      'followers_count' => $followers_count,
      'newPosts' => $newPosts,
      'startDate'=> $startDate, 
      'endDate'=> $endDate, 
    ]);
  }


  private function cacheRecentHub($communityId, $communityName)
  {
    $userId = Auth::check() ? Auth::user()->id : null; // Check if the user is authenticated
    $cacheKey = $userId ? "recent_hubs:{$userId}" : "recent_hubs:guest";

    $hubData = ['id' => $communityId, 'name' => $communityName];

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
      'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048', // added more formats
      'moderators' => 'nullable|array',
      'moderators.*' => 'exists:authenticated_users,id'
    ]);

    $image_id = null;
    if ($request->hasFile('image')) {
      $file = $request->file('image');

      // Generate a random value for the filename
      $randomValue = uniqid();

      // Get the file extension dynamically (jpeg, png, gif, etc.)
      $extension = $file->extension();

      // Construct the filename with the dynamic extension
      $filename = 'hub' . $randomValue . '.' . $extension;

      // Move the uploaded file to the 'images' directory in the base path
      $file->move(base_path('images'), $filename);

      // Create the image record in the database
      $image = Image::create([
        'path' => 'images/' . $filename
      ]);

      // Get the image_id from the created image record
      $image_id = $image->id;
    }

    // Create the community
    $community = Community::create([
      'name' => $request->name,
      'description' => $request->description,
      'privacy' => $request->privacy === 'private',
      'image_id' => $image_id, // Set the image_id if there is an image
      'creation_date' => now(),
    ]);

    // Attach the authenticated user as the moderator
    $community->moderators()->attach(Auth::user()->id);

    // If there are additional moderators, attach them as well
    if ($request->has('moderators')) {
      $community->moderators()->attach($request->moderators);
    }

    return redirect()->route('communities.show', ['id' => $community->id]);
  }




  public function join($id)
  {
    $community = Community::findOrFail($id);
    if (!auth()->user()->communities()->where('community_id', $id)->exists()) {
      auth()->user()->communities()->attach($id);
      return redirect()->back()->with('success', 'Successfully joined the community!');
    }

    return redirect()->back()->with('error', 'You are already following this community.');
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

  /*
    public function apply(Request $request, $id)
    {
        $community = Community::find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        if ($community->privacy === 'private') {
            return response()->json(['message' => 'You cannot directly join a private community'], 403);
        }

        $authUser = Auth::user();
        $alreadyJoined = $community->followers()->where('authenticated_user_id', $authUser->id)->exists();

        if ($alreadyJoined) {
            return response()->json(['message' => 'You are already a member of this community'], 400);
        }

        // Adicionar o usuário à lista de seguidores da comunidade
        $community->followers()->attach($authUser->id);

        return response()->json(['message' => 'Your application to join the community has been submitted']);
    }*/
}
