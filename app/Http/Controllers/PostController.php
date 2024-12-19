<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\News;
use App\Models\Topic;
use App\Models\Vote;
use App\Models\PostVote;
use App\Models\Comment;
use App\Models\Community;
use App\Models\CommentVote;
use App\Models\Notification;
use App\Models\PostNotification;
use App\Models\UpvoteNotification;
use App\Models\AuthenticatedUser;
use DOMDocument;
use DOMXPath;

class PostController extends Controller
{
  public function show($id)
  {
    $post = Post::findOrFail($id);
    if(!$post -> news()){return  redirect()->route('topic.show', $id);}
    else{return redirect()->route('news.show', $id);}
  }

  public function getOgTags($newsURL)
  {

    libxml_use_internal_errors(true);

    // create curl resource
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $newsURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");

    $c = curl_exec($ch);

    curl_close($ch);

    $d = new DomDocument();
    $d->loadHTML($c);
    $xp = new domxpath($d);

    $ogTags = [];

    $imageElement = $xp->query("//meta[@property='og:image']")->item(0);
    if ($imageElement) {
      $ogTags['image'] = $imageElement->getAttribute("content");
    }

    $titleElement = $xp->query("//meta[@property='og:title']")->item(0);
    if ($titleElement) {
      $ogTags['title'] = $titleElement->getAttribute("content");
    }

    return $ogTags;
  }

  private function notifyCommunityFollowers($communityId, $post)
  {
    $community = Community::find($communityId);

    // Retrieve followers of the community
    $followers = $community->followers;

    // Get the authors of the post
    $authors = $post->authors->pluck('id')->toArray(); // Get author IDs

    foreach ($followers as $follower) {
      // Skip notifying if the follower is an author of the post
      if (in_array($follower->id, $authors)) {
        continue;
      }

      // Create a notification for each follower
      $notification = Notification::create([
        'is_read' => false,
        'notification_date' => now(),
        'authenticated_user_id' => $follower->id,
      ]);

      // Link the notification to the post
      PostNotification::create([
        'notification_id' => $notification->id,
        'post_id' => $post->id,
      ]);
    }
  }

  private function notifyFollowers($userId, $post)
  {
    $user = AuthenticatedUser::find($userId);

    // Retrieve followers of the user
    $followers = $user->followers;

    // Get the authors of the post
    $authors = $post->authors->pluck('id')->toArray(); // Get author IDs

    foreach ($followers as $follower) {
      // Skip notifying if the follower is an author of the post
      if (in_array($follower->id, $authors)) {
        continue;
      }

      // Create a notification for each follower
      $notification = Notification::create([
        'is_read' => false,
        'notification_date' => now(),
        'authenticated_user_id' => $follower->id,
      ]);

      // Link the notification to the post
      PostNotification::create([
        'notification_id' => $notification->id,
        'post_id' => $post->id,
      ]);
    }
  }


  public function createPost()
  {
    return view('pages.create_post');
  }

  public function removeAuthors(Request $request, $postId)
  {
      $post = Post::findOrFail($postId);
      $authorsToRemove = $request->input('authors_to_remove', []);

      if (empty($authorsToRemove)) {
          return response()->json(['message' => 'Nenhum autor selecionado'], 400);
      }

      if ($post->authors()->count() <= count($authorsToRemove)) {
          return response()->json(['message' => 'Não é possível remover todos os autores'], 400);
      }

      try {
          $post->authors()->detach($authorsToRemove);

          return response()->json([
              'message' => 'Autores removidos com sucesso',
              'removed_authors' => $authorsToRemove
          ]);
      } catch (\Exception $e) {
          return response()->json([
              'message' => 'Falha ao remover autores: ' . $e->getMessage()
          ], 500);
      }
  }

  public function create(Request $request)
  {
      $request->validate([
          'title' => 'nullable|string|max:255',
          'content' => 'required|string',
          'community_id' => 'required|exists:communities,id',
          'type' => 'required|in:news,topic',
          'authors' => 'nullable|array',
          'authors.*' => 'exists:authenticated_users,id', 
      ]);

    $title = $request->title ?? "News";

      $post = Post::create([
          'title' => $title,
          'content' => $request->content,
          'community_id' => $request->community_id,
      ]);

      $post->authors()->attach(Auth::user()->id, ['pinned' => false]);
      
      if ($request->has('authors'))
        $post->authors()->attach($request->authors, ['pinned' => false]);

      $this->notifyCommunityFollowers($request->community_id, $post);
      $this->notifyFollowers(Auth::user()->id, $post);

      if ($request->type === 'news') {
          $ogTags = $this->getOgTags($request->news_url);
          $post->title = $request->title ?? $ogTags['title'] ?? "News";
          $post->save();

          return app(NewsController::class)->createNews($post, $request->news_url, $ogTags['image'] ?? null);
      } elseif ($request->type === 'topic') {
        $post->title = $request->title;
          return app(TopicController::class)->createTopic($post);
      }

      return response()->json(['message' => 'Invalid type'], 400);
  }


  public function destroy(Request $request, $id)
  {
    $post = Post::find($id);

    if (!$post) {
      return response()->json(['message' => 'Post not found'], 404);
    }

    $user = Auth::user();

    if (!$post->authors->contains($user->id)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    if ($post->votes()->exists() || $post->comments()->exists()) {
      return response()->json(['message' => 'Post cannot be deleted as it has votes or comments'], 400);
    }

    $post->authors()->detach();
    $post->delete();

    return response()->json(['message' => 'Post deleted successfully'], 200);
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
      if (!is_numeric($post_id)) {
        return response()->json(['message' => 'Invalid post ID'], 400);
    }

    $post = Post::findOrFail($post_id);
    $user = Auth::user();
    $voteType = $request->input('vote_type');

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
      $vote = Vote::create(['upvote' => true, 'authenticated_user_id' => $user->id]);
      PostVote::insert([
        'vote_id' => $vote->id,
        'post_id' => $post->id,
      ]);

      $newScore++;

      foreach ($post->authors as $author) {
        if ($author->id != $user->id) { // Don't notify the user who voted
          $notification = Notification::create([
            'is_read' => false,
            'notification_date' => now(),
            'authenticated_user_id' => $author->id,
          ]);

          UpvoteNotification::create([
            'notification_id' => $notification->id,
            'vote_id' => $vote->id,
          ]);
        }
      }

      return response()->json([
        'status' => 'created',
        'message' => 'Post upvoted successfully.',
        'vote' => 'upvote',
        'newScore' => $newScore
      ]);
    } else if ($voteType === 'downvote') {
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
    ], 400);
  }
}
