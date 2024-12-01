<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\News;
use App\Models\Post;
use App\Models\Vote;
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

        return redirect()->route('news')->with('success', 'Topic created successfully');

    }
    public function show($post_id)
    {
        // Retrieve the topic using the post ID
        $topicItem = Topic::with('post.community')
            ->where('post_id', $post_id)
            ->firstOrFail();

        // Get upvote and downvote counts
        $topicItem->upvotes_count = Vote::whereHas('postVote', function ($query) use ($topicItem) {
            $query->where('post_id', $topicItem->post_id);
        })->where('upvote', true)->count();

        $topicItem->downvotes_count = Vote::whereHas('postVote', function ($query) use ($topicItem) {
            $query->where('post_id', $topicItem->post_id);
        })->where('upvote', false)->count();

        // Calculate the score
        $topicItem->score = $topicItem->upvotes_count - $topicItem->downvotes_count;

        // Get the currently authenticated user
        if(Auth::check()){
        $authUser = Auth::user();
        $userVote = $authUser->votes()
            ->whereHas('postVote', function ($query) use ($topicItem) {
                $query->where('post_id', $topicItem->post_id);
            })
            ->first();
        }
        else{$userVote = NULL;}

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

}
