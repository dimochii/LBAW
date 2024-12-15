<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\CommunityFollowRequest;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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



  public function show($id)
  {

    $community = Community::with(['posts', 'posts.authors', 'posts.votes', 'posts.comments'])
    ->findOrFail($id);

    if (!$community) {
      abort(404, 'Community not found');
    }

    $this->cacheRecentHub($community->id, $community->name);

    $posts = $community->posts->map(function ($post) {
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
        'news' => $post->news,  
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

    return view('pages.hub', [
      'community' => $community,
      // 'posts' => $posts,
      'newsPosts' => $newsPosts,
      'topicPosts' => $topicPosts,
      'is_following' => $is_following,
      'posts_count' => $posts_count,
      'followers_count' => $followers_count
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
  

  

    public function join($id) {
      $community = Community::findOrFail($id);
      $user = auth()->user();

      if (!$community->privacy) {
        if (!auth()->user()->communities()->where('community_id', $id)->exists()) {
          auth()->user()->communities()->attach($id);
          return redirect()->back()->with('success', 'Successfully joined the community!');
        }
        else {
          return redirect()->back()->with('error', 'You are already following this community.');
        }
      }

      if (CommunityFollowRequest::where('community_id', $id)
        ->where('authenticated_user_id', auth()->user()->id)
        ->where('request_status', 'pending')
        ->exists()) {
        return redirect()->back()->with('error', 'Você já fez uma solicitação para seguir esta comunidade.');
      }

      
      $request= CommunityFollowRequest::create([
          'authenticated_user_id' => $user->id,
          'community_id' => $id,
          'request_status' => 'pending',
          'request_date' => now(),
      ]);

      return redirect()->back()->with('success', 'Sua solicitação foi enviada e está aguardando aprovação.');
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


  public function acceptFollowRequest($requestId) {
      $request = CommunityFollowRequest::findOrFail($requestId);
      $this->authorize('isCommunityAdmin', $request->community);

      $request->request_status = 'accepted';
      $request->save();

      $request->community->followers()->attach($request->user_id);

      return redirect()->route('communities.manageFollowRequests', ['id' => $request->community_id])
          ->with('success', 'Follow request accepted.');
  }

  public function rejectFollowRequest($requestId) {
      $request = CommunityFollowRequest::findOrFail($requestId);
      $this->authorize('isCommunityAdmin', $request->community);

      $request->request_status = 'rejected';
      $request->save();

      return redirect()->route('communities.manageFollowRequests', ['id' => $request->community_id])
          ->with('success', 'Follow request rejected.');
  }

}
