<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\News;
use App\Models\Post;
use App\Models\Vote;
use App\Models\Topic;
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

      $posts = Post::all();
  
        
        $news = $posts->filter(function ($post) {
          return !is_null($post['news']);
        });

        $topics = $posts->filter(function ($post) {
          return !is_null($post['topic']);
      });
  
      return view('pages.news', [
        'news' => $news,
        'topics' => $topics
      ]);
  }
  


  public function show($post_id)
  {
    $newsItem = News::with('post.community')
      ->where('post_id', $post_id)
      ->firstOrFail();
    $post = Post::findOrFail($post_id);

    $newsItem->upvotes_count =  $post->getUpvoteCountAttribute();

    $newsItem->downvotes_count =  $post->getDownvoteCountAttribute();
  

    $newsItem->score = $newsItem->upvotes_count - $newsItem->downvotes_count;

    if(Auth::check()) {
      $authUser = Auth::user();
      $userVote = $authUser->votes()
        ->whereHas('postVote', function ($query) use ($newsItem) {
          $query->where('post_id', $newsItem->post_id);
        })
        ->first();
    }
    else{$userVote = NULL;}

    if ($userVote) {
      $newsItem->user_upvoted = $userVote->upvote;
      $newsItem->user_downvoted = !$userVote->upvote;  
    } else {
      $newsItem->user_upvoted = false;
      $newsItem->user_downvoted = false;
    }

    $newsItem->comments_count = Comment::where('post_id', $newsItem->post->id)->count();

    $comments = Comment::with('user') 
      ->where('post_id', $newsItem->post->id)
      ->orderBy('creation_date', 'asc')
      ->get();

    return view('pages.newsitem', compact('newsItem', 'comments'));
  }


  public function createNews(Post $post, $newsUrl, $imageUrl = null)
  {
    $news = News::create([
      'post_id' => $post->id,
      'news_url' => $newsUrl,
      'image_url' => $imageUrl
    ]);

    return redirect()->route('news.show', $post->id)->with('success', 'News post created successfully.');
  }

  public function edit($post_id)
  {
    $post = Post::findOrFail($post_id);
    $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();

    $this->authorize('isAuthor', $post);

    return view('pages.edit_news', compact('newsItem'));
  }

  public function update(Request $request, $post_id)
  {
    $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();

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
      'content' => trim($request->content),
    ]);

    $newsItem->update([
      'news_url' => $request->news_url,
    ]);

    return redirect()->route('news.show', $post_id)->with('success', 'News updated successfully');
  }


}
