<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\News;
use App\Models\Post;
use App\Models\Vote;
use App\Models\PostVote;
use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Http\Request;

class NewsController extends Controller
{
  /**
   * Display a listing of all news articles.
   */
  public function list()
  {
    $news = News::with('post')->get();
    $authUser = Auth::user();
    foreach ($news as $item) {
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

    return view('pages.news', [
      'news' => $news
    ]);
  }


  public function show($post_id)
  {
    $newsItem = News::with('post.community')
      ->where('post_id', $post_id)
      ->firstOrFail();

    // Get upvote and downvote counts
    $newsItem->upvotes_count = Vote::whereHas('postVote', function ($query) use ($newsItem) {
      $query->where('post_id', $newsItem->post_id);
    })->where('upvote', true)->count();

    $newsItem->downvotes_count = Vote::whereHas('postVote', function ($query) use ($newsItem) {
      $query->where('post_id', $newsItem->post_id);
    })->where('upvote', false)->count();

    // Calculate the score
    $newsItem->score = $newsItem->upvotes_count - $newsItem->downvotes_count;

    // Get the currently authenticated user
    $authUser = Auth::user();

    // Check if the authenticated user has voted on this post
    $userVote = $authUser->votes()
      ->whereHas('postVote', function ($query) use ($newsItem) {
        $query->where('post_id', $newsItem->post_id);
      })
      ->first();

    // Determine if the user has upvoted or downvoted the post
    if ($userVote) {
      $newsItem->user_upvoted = $userVote->upvote;
      $newsItem->user_downvoted = !$userVote->upvote;  // If it's not an upvote, it's a downvote
    } else {
      // User has not voted on this post
      $newsItem->user_upvoted = false;
      $newsItem->user_downvoted = false;
    }

    // Get comments for the post
    $newsItem->comments_count = Comment::where('post_id', $newsItem->post->id)->count();

    $comments = Comment::with('user') // Eager load the user who made the comment
      ->where('post_id', $newsItem->post->id)
      ->orderBy('creation_date', 'asc')
      ->get();

    // Pass the comments and news item to the view
    return view('pages.newsitem', compact('newsItem', 'comments'));
  }


  public function createNews(Post $post, $newsUrl)
  {
    $news = News::create([
      'post_id' => $post->id,
      'news_url' => $newsUrl,
    ]);

    return redirect()->route('news')->with('success', 'News created successfully');
  }

  public function edit($post_id)
  {
    $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();

    // Ensure the current user is an author of the post
    if (!$newsItem->post->authors->contains('id', Auth::user()->id)) {
      abort(403, 'Unauthorized');
    }

    return view('pages.edit_news', compact('newsItem'));
  }

  public function update(Request $request, $post_id)
  {
    $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();

    // Ensure the current user is an author of the post
    if (!$newsItem->post->authors->contains('id', Auth::user()->id)) {
      abort(403, 'Unauthorized');
    }

    $request->validate([
      'title' => 'required|string|max:255',
      'content' => 'required|string',
      'news_url' => 'required|url',
    ]);

    $newsItem->post->update([
      'title' => $request->title,
      'content' => $request->content,
    ]);

    $newsItem->update([
      'news_url' => $request->news_url,
    ]);

    return view('pages.newsitem', compact('newsItem'));
  }



  public function upvote($post_id)
  {
    $post = Post::findOrFail($post_id);
    $user = Auth::user();

    $existingVote = $post->votes()->where('authenticated_user_id', $user->id)->first();

    if ($existingVote) {
      if ($existingVote->upvote) {
        return redirect()->back()->with('success', 'You have already upvoted this post.');
      }

      $existingVote->update(['upvote' => true]);
    } else {
      $vote = Vote::create(['upvote' => true, 'authenticated_user_id' => $user->id]);
      PostVote::insert([
        'vote_id' => $vote->id,
        'post_id' => $post->id,
      ]);
    }

    return redirect()->back()->with('success', 'Post upvoted successfully.');
  }

  public function downvote($post_id)
  {
    $post = Post::findOrFail($post_id);
    $user = Auth::user();

    $existingVote = $post->votes()->where('authenticated_user_id', $user->id)->first();

    if ($existingVote) {
      if (!$existingVote->upvote) {
        return redirect()->back()->with('success', 'You have already downvoted this post.');
      }
      $existingVote->update(['upvote' => false]);
    } else {
      $vote = Vote::create(['upvote' => false, 'authenticated_user_id' => $user->id]);
      PostVote::insert([
        'vote_id' => $vote->id,
        'post_id' => $post->id,
      ]);
    }

    return redirect()->back()->with('success', 'Post downvoted successfully.');
  }


  public function voteUpdate(Request $request, $post_id)
  {
    $post = Post::findOrFail($post_id);
    $user = Auth::user();
    $voteType = $request->input('vote_type'); // 'upvote', 'downvote'

    // Retrieve existing vote if it exists
    $existingVote = $post->votes()->where('authenticated_user_id', $user->id)->first();

    $newScore = 0;

    if ($existingVote) {
      if (($voteType === 'upvote' && $existingVote->upvote) || ($voteType === 'downvote' && !$existingVote->upvote)) {
        $existingVote->postVote()->delete();
        $existingVote->voteNotification()->delete();
        $existingVote->delete();

        if (!$existingVote->upvote) $newScore++;
        else $newScore--;

        return response()->json([
          'status' => 'removed',
          'message' => 'Upvote removed',
          'vote' => null,
          'newScore' => $newScore
        ]);
      } else if (($voteType === 'upvote' && !$existingVote->upvote) || ($voteType === 'downvote' && $existingVote->upvote)) {
        $existingVote->postVote()->delete();
        $existingVote->voteNotification()->delete();
        $existingVote->delete();
        
        if (!$existingVote->upvote) $newScore++;
        else $newScore--;
      }
    }

    if ($voteType === 'upvote') {
      // create upvote
      $vote = Vote::create(['upvote' => true, 'authenticated_user_id' => $user->id]);
      PostVote::insert([
        'vote_id' => $vote->id,
        'post_id' => $post->id,
      ]);

      $newScore++;

      return response()->json([
        'status' => 'created',
        'message' => 'Post upvoted successfully.',
        'vote' => 'upvote', 
        'newScore' => $newScore
      ]);
    } else if ($voteType === 'downvote') {
      // create downvote
      $vote = Vote::create(['upvote' => false, 'authenticated_user_id' => $user->id]);
      PostVote::insert([
        'vote_id' => $vote->id,
        'post_id' => $post->id,
      ]);

      $newScore--;

      return response()->json([
        'status' => 'created',
        'message' => 'Post downvoted successfully.',
        'vote' => 'downvote',
        'newScore' => $newScore
      ]);
    }

    return response()->json([
      'status' => 'error',
      'message' => 'Invalid action.',
      'vote' => null,
    ], 400); // Bad request
  }
}
