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

    public function store(Request $request, $post_id) {
        $validatedData = $request->validate([
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);
    
        // Sanitize content to prevent XSS
        $content = e($validatedData['content']); 
    
        $post = Post::find($post_id);
    
        if (!$post) {
            return response()->view('errors.404', [], 404); 
        }
    
        $comment = Comment::create([
            'content' => $content, // Store sanitized content
            'post_id' => $post_id, 
            'authenticated_user_id' => auth()->user()->id, 
            'parent_comment_id' => $validatedData['parent_comment_id'], 
            'creation_date' => now(), 
            'updated' => false,
        ]);
    
        $authors = $post->authors()->where('authenticated_user_id', '!=', auth()->user()->id)->get();
    
        // Create a notification for each author
        foreach ($authors as $author) {
            $notification = Notification::create([
                'is_read' => false,
                'notification_date' => now(),
                'authenticated_user_id' => $author->id, 
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
            return response()->view('errors.404', [], 404); 
        }
        if ($comment->authenticated_user_id !== Auth::id()) {
            return response()->view('errors.403', [], 403); 
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

    public function delete($id) {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'Comment not found.'], 404);
        }

        $user = Auth::user();

        if (!$user->is_admin && $user->id != $comment->authenticated_user_id) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to delete this comment.'], 403);
        }

        $comment->authenticated_user_id = 1;
        $comment->content = 'This comment has been deleted';
        $comment->save();

        return response()->json(['success' => true, 'message' => 'Comment updated successfully.']);
    }

}



