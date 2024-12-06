<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use App\Models\Vote;
use App\Models\Topic;
use App\Models\News;

use App\Models\PostVote;

class FeedController extends Controller
{
  // Fetch posts from user's communities created in the last 72 hours, order them by vote quantity, caches values for 60mins
  public function home()
  {

    $cachedPosts = Cache::get('user_posts');

    if ($cachedPosts) {
      return response()->json($cachedPosts);
    }

    $authUser = Auth::user();


    $posts = Post::withCount('votes')
      ->whereIn('community_id', $authUser->communities->pluck('id'))
      ->where('creation_date', '>', now()->subHours(72))
      ->orderBy('votes_count', 'desc')
      ->get();

    foreach ($posts as $item) {
      $item->upvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->post_id);
      })->where('upvote', true)->count();

      $item->downvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->post_id);
      })->where('upvote', false)->count();

      $userVote = $authUser->votes()->whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->post_id);
      })->first();

      if ($userVote) {
        $item->user_upvoted = $userVote->upvote;
        $item->user_downvoted = !$userVote->upvote;
      } else {
        // User hasn't voted on this post
        $item->user_upvoted = false;
        $item->user_downvoted = false;
      }
    }

    return view('pages.home', [
      'posts' => $posts
    ]);
  }
  public function global()
  {
    // Check if cached posts exist
    // $cachedPosts = Cache::get('popular_posts');

    // if ($cachedPosts) {
    //   return view('pages.global', [
    //     'posts' => $cachedPosts
    //   ]);
    // }

    // Fetch posts from public communities created within the last 72 hours
    $posts = Post::withCount('votes')
      ->whereHas('community', function ($query) {
        $query->where('privacy', false);
      })
      ->where('creation_date', '>', now()->subHours(72))
      ->orderBy('votes_count', 'desc')
      ->get();

    $authUser = Auth::user(); // For retrieving user-specific votes

    foreach ($posts as $item) {
      // Count upvotes and downvotes
      $item->upvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->id);
      })->where('upvote', true)->count();

      $item->downvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
        $query->where('post_id', $item->id);
      })->where('upvote', false)->count();

      // Check if the authenticated user has voted on this post
      if ($authUser) {
        $userVote = $authUser->votes()->whereHas('postVote', function ($query) use ($item) {
          $query->where('post_id', $item->id);
        })->first();

        if ($userVote) {
          $item->user_upvoted = $userVote->upvote;
          $item->user_downvoted = !$userVote->upvote;
        } else {
          // User hasn't voted on this post
          $item->user_upvoted = false;
          $item->user_downvoted = false;
        }
      } else {
        // For guests, no votes are possible
        $item->user_upvoted = false;
        $item->user_downvoted = false;
      }
    }

    // Cache the posts for 60 minutes
    // Cache::put('popular_posts', $posts, 60);

    // Render the view and pass the posts
    return view('pages.global', [
      'posts' => $posts
    ]);
  }


  public function recent()
  {
    $authUser = Auth::user(); // For retrieving user-specific votes

    // Fetch posts from user's communities, sorted by creation date
    $posts = Post::withCount('votes')
      ->whereIn('community_id', $authUser->communities->pluck('id'))
      ->orderBy('creation_date', 'desc')
      ->get();

    foreach ($posts as $post) {
      // Count upvotes and downvotes
      $post->upvotes_count = Vote::whereHas('postVote', function ($query) use ($post) {
        $query->where('post_id', $post->id);
      })->where('upvote', true)->count();

      $post->downvotes_count = Vote::whereHas('postVote', function ($query) use ($post) {
        $query->where('post_id', $post->id);
      })->where('upvote', false)->count();

      // Check if the authenticated user has voted on this post
      $userVote = $authUser->votes()->whereHas('postVote', function ($query) use ($post) {
        $query->where('post_id', $post->id);
      })->first();

      if ($userVote) {
        $post->user_upvoted = $userVote->upvote;
        $post->user_downvoted = !$userVote->upvote;
      } else {
        // User hasn't voted on this post
        $post->user_upvoted = false;
        $post->user_downvoted = false;
      }
    }

    // Render the view and pass the posts collection
    return view('pages.recent', [
      'posts' => $posts,
    ]);
  }

  public function aboutUs() {
    return view('pages.about_us');
  }

  public function admin() {
    return view('pages.admin');
  }


  public function bestof()
  {
      // 10 topics
      $topTopics = Topic::select('topics.*')
          ->addSelect([
              'votes_count' => function ($query) {
                  $query->selectRaw('COUNT(*)')
                      ->from('votes')
                      ->join('post_votes', 'post_votes.vote_id', '=', 'votes.id')
                      ->whereColumn('topics.post_id', 'post_votes.post_id');
              }
          ])
          ->orderBy('votes_count', 'desc')
          ->limit(10)
          ->get();
  
      // 10 news
      $topNews = News::select('news.*')
          ->addSelect([
              'votes_count' => function ($query) {
                  $query->selectRaw('COUNT(*)')
                      ->from('votes')
                      ->join('post_votes', 'post_votes.vote_id', '=', 'votes.id')
                      ->whereColumn('news.post_id', 'post_votes.post_id');
              }
          ])
          ->orderBy('votes_count', 'desc')
          ->limit(10)
          ->get();
  
      
      return view('pages.bestof', [
          'topTopics' => $topTopics,
          'topNews' => $topNews,
      ]);
  }
  


}
