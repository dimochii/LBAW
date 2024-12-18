<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\News;
use App\Models\Post;
use App\Models\Vote;
use App\Enums\TopicStatus;
use App\Models\PostVote;
use App\Models\Comment;
use App\Models\CommentVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TopicController extends Controller
{
    public function createTopic(Post $post)
    {
        $topic = Topic::create([
            'post_id' => $post->id,
        ]);

        return redirect()->route('global')->with('success', 'Topic created successfully, waiting approval.');
    }

    /**
     * Display a single topic with its details, including votes, comments, and more.
     */
    public function show($post_id)
    {
        if(Auth::user()->is_suspended) {

            return view('pages.suspension');
          }
        
        // Retrieve the topic using the post ID
        $topicItem = Topic::with('post.community')
            ->where('post_id', $post_id)
            ->firstOrFail();
        if((!Auth::user()->is_admin) && ($topicItem->status != 'accepted')){
            return response()->view('errors.403', [], 403);
        }
        // Get upvote and downvote counts
        $topicItem->upvotes_count = Vote::whereHas('postVote', function ($query) use ($topicItem) {
            $query->where('post_id', $topicItem->post_id);
        })->where('upvote', true)->count();

        $topicItem->downvotes_count = Vote::whereHas('postVote', function ($query) use ($topicItem) {
            $query->where('post_id', $topicItem->post_id);
        })->where('upvote', false)->count();

        // Calculate the score
        $topicItem->score = $topicItem->upvotes_count - $topicItem->downvotes_count;

        // Check if the user is logged in
        if (Auth::check()) {
            $authUser = Auth::user();
            $userVote = $authUser->votes()
                ->whereHas('postVote', function ($query) use ($topicItem) {
                    $query->where('post_id', $topicItem->post_id);
                })
                ->first();
        } else {
            $userVote = null;
        }

        // Determine if the user has upvoted or downvoted the post
        if ($userVote) {
            $topicItem->user_upvoted = $userVote->upvote;
            $topicItem->user_downvoted = !$userVote->upvote;  // If it's not an upvote, it's a downvote
        } else {
            // User has not voted on this post
            $topicItem->user_upvoted = false;
            $topicItem->user_downvoted = false;
        }

        // Get comments for the post (which is related to the Topic)
        $topicItem->comments_count = Comment::where('post_id', $topicItem->post->id)->count();

        $comments = Comment::with('user') 
            ->where('post_id', $topicItem->post->id)
            ->orderBy('creation_date', 'asc')
            ->get();

        // Pass the comments and topic item to the view
        return view('pages.topicitem', compact('topicItem', 'comments'));
    }

    /**
     * Display a listing of all topics.
     */
    public function list()
    {
        if(Auth::user()->is_suspended) {

            return view('pages.suspension');
        }

        $topics = Topic::with('post')->get();
        
        foreach ($topics as $item) {
            $post = $item->post;
            $item->upvotes_count = $post->getUpvoteCountAttribute();
            $item->downvotes_count = $post->getDownvoteCountAttribute();

            if (Auth::check()) {
                $userVote = $post->userVote(Auth::user()->id);
                $item->user_upvoted = $userVote?->upvote ?? false;
                $item->user_downvoted = $userVote ? !$userVote->upvote : false;
            } else {
                $item->user_upvoted = false;
                $item->user_downvoted = false;
            }
        }

        return view('pages.topics', compact('topics'));
    }

    /**
     * Edit an existing topic.
     */
    public function edit($post_id)
    {
        if(Auth::user()->is_suspended) {

            return view('pages.suspension');
        
        }
        $post = Post::findOrFail($post_id);
        $topicItem = Topic::with('post')->where('post_id', $post_id)->firstOrFail();

        $this->authorize('isAuthor', $post);

        return view('pages.edit_topic', compact('topicItem'));
    }

    /**
     * Update an existing topic.
     */
    public function update(Request $request, $post_id)
    {
        $topicItem = Topic::with('post')->where('post_id', $post_id)->firstOrFail();

        if (!$topicItem->post->authors->contains('id', Auth::user()->id)) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'in:' . implode(',', TopicStatus::getValues()), // Ensure valid status
        ]);
    
        $data['status'] = $data['status'] ?? TopicStatus::Pending->value;
    
        $topicItem->post->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $topicItem->update([
            'status' => $data['status'],
        ]);
        return redirect()->route('news')->with('success', 'Topic updated successfully');
    }

    public function accept($id)
    {
      $topicItem = Topic::with('post')->where('post_id', $id)->firstOrFail();
      $topicItem->status = TopicStatus::Accepted->value;
      $topicItem->save();

      return response()->json(['status' => 'ok'], 200);
    }

    public function reject($id)
    {
      $topicItem = Topic::with('post')->where('post_id', $id)->firstOrFail();
      $topicItem->status = TopicStatus::Rejected->value;
      $topicItem->save();

      return response()->json(['status' => 'ok'], 200);
    }
}
