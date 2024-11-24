<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

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

        $comment = Comment::create([
          'content' => $validatedData['content'], 
          'post_id' => $post_id, // The ID of the post this comment belongs to
          'authenticated_user_id' => auth()->user()->id, // The authenticated user's ID
          'parent_comment_id' => $validatedData['parent_comment_id'], // If it's a reply, provide the parent comment ID
          'creation_date' => now(), // Current timestamp
          'updated' => false, // Current timestamp (if you're using `updated_at`, replace with that)
      ]);

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

        // Check if person who wants to edit is the actual owner 
        if ($comment->authenticated_user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update the content
        $comment->content = $request->content;
        $comment->updated = true;
        $comment->save();

        return response()->json([
            'message' => 'Comment updated successfully!',
            'comment' => $comment,
        ], 200);
    }
}



