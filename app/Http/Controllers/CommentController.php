<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\CommentNotification;
use App\Models\Notification; // Assuming you have a Notification model for general notifications
use App\Models\AuthenticatedUser; // To get the authors of the post
use App\Models\CommentVote;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function getComments($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $comments = $post->comments()->with('user')->get();
        $commentTree = $this->buildCommentTree($comments);
        // $commentTree = Comment::whereNull('parent_comment_id')
        //     ->where('post_id', $id)
        //     ->with('children')
        //     ->get();
        
        return response()->json($commentTree, 200);
    }

    private function buildCommentTree($comments, $parentId = null)
    {
        return $comments->filter(function ($comment) use ($parentId) {
            return $comment->parent_comment_id === $parentId;
        })->map(function ($comment) use ($comments) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_id' => $comment->user->id,
                'user' => $comment->user->name,
                'created_at' => $comment->creation_date,
                'updated' => $comment->updated,
                'children' => $this->buildCommentTree($comments, $comment->id), 
            ];
        })->values();
    }

    public function store(Request $request, $post_id) {
        $validatedData = $request->validate([
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);
    
        // Retrieve the post being commented on
        $post = Post::find($post_id);
    
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
    
        // Create the comment
        $comment = Comment::create([
            'content' => $validatedData['content'], 
            'post_id' => $post_id, // The ID of the post this comment belongs to
            'authenticated_user_id' => auth()->user()->id, // The authenticated user's ID
            'parent_comment_id' => $validatedData['parent_comment_id'], // If it's a reply, provide the parent comment ID
            'creation_date' => now(), // Current timestamp
            'updated' => false, // Set to false initially
        ]);
    
        // Get the authors of the post (excluding the commenter to avoid sending notifications to the commenter)
        $authors = $post->authors()->where('authenticated_user_id', '!=', auth()->user()->id)->get();
    
        // Create a notification for each author
        foreach ($authors as $author) {
            // Create a Notification (this could be a general notification, or a custom notification)
            $notification = Notification::create([
                'is_read' => false,
                'notification_date' => now(),
                'authenticated_user_id' => $author->id, // The author receiving the notification
                // Add other fields if necessary
            ]);
    
            // Link the notification to the comment
            $commentNotification = CommentNotification::create([
                'notification_id' => $notification->id,
                'comment_id' => $comment->id,
            ]);
        }
    
        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'user' => auth()->user()->name,
                'content' => $comment->content,
                'created_at' => $comment->created_at
            ]
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        
        if ($comment->authenticated_user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        
        $comment->content = $request->content;
        $comment->updated = true;
        $comment->save();

        return response()->json([
            'message' => 'Comment updated successfully!',
            'comment' => $comment,
        ], 200);
    }

    public function upvote($post_id, $comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        $user = Auth::user();
    
        $existingVote = $comment->votes()->where('authenticated_user_id', $user->id)->first();
    
        if ($existingVote) {
            if ($existingVote->upvote) {
                return redirect()->back()->with('success', 'You have already upvoted this comment.');
            }
            $existingVote->update(['upvote' => true]);
        } else {
            $vote = Vote::create(['upvote' => true, 'authenticated_user_id' => $user->id]);
            CommentVote::create([
                'vote_id' => $vote->id,
                'comment_id' => $comment->id,
            ]);
        }
    
        return redirect()->back()->with('success', 'Comment upvoted successfully.');
    }

    public function downvote($post_id, $comment_id)
{
    $comment = Comment::findOrFail($comment_id);
    $user = Auth::user();

    $existingVote = $comment->votes()->where('authenticated_user_id', $user->id)->first();

    if ($existingVote) {
        if (!$existingVote->upvote) {
            return redirect()->back()->with('success', 'You have already downvoted this comment.');
        }
        $existingVote->update(['upvote' => false]);
    } else {
        $vote = Vote::create(['upvote' => false, 'authenticated_user_id' => $user->id]);
        CommentVote::create([
            'vote_id' => $vote->id,
            'comment_id' => $comment->id,
        ]);
    }

    return redirect()->back()->with('success', 'Comment downvoted successfully.');
}

public function voteUpdate(Request $request, $comment_id)
{
    $comment = Comment::findOrFail($comment_id);
    $user = Auth::user();
    $voteType = $request->input('vote_type');

    $existingVote = $comment->votes()->where('authenticated_user_id', $user->id)->first();
    $newScore = $comment->upvotesCount()->count() - $comment->downvotesCount()->count();

    if ($existingVote) {
        if (($voteType === 'upvote' && $existingVote->upvote) || ($voteType === 'downvote' && !$existingVote->upvote)) {
            // Remove the vote
            $existingVote->commentVote()->delete();
            $existingVote->delete();

            $newScore += $existingVote->upvote ? -1 : 1;

            return response()->json([
                'vote' => null,
                'status' => 'removed',
                'message' => 'Vote removed successfully.',
                'newScore' => $newScore
            ]);
        }

        // Change the vote type
        $existingVote->update(['upvote' => $voteType === 'upvote']);
        $newScore += $voteType === 'upvote' ? 2 : -2;
    } else {
        // Add a new vote
        $vote = Vote::create(['upvote' => $voteType === 'upvote', 'authenticated_user_id' => $user->id]);
        CommentVote::create([
            'vote_id' => $vote->id,
            'comment_id' => $comment->id,
        ]);

        $newScore += $voteType === 'upvote' ? 1 : -1;
    }

    return response()->json([
        'vote' => $voteType,
        'status' => 'success',
        'message' => $voteType === 'upvote' ? 'Comment upvoted successfully.' : 'Comment downvoted successfully.',
        'newScore' => $newScore
    ]);
}


}



