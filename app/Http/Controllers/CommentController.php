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
                'updated_at' => $comment->updated,
                'children' => $this->buildCommentTree($comments, $comment->id), 
            ];
        })->values();
    }

    public function store(Request $request)
    {
        //validation
        $request->validate([
            'content' => 'required|string|max:1000',
            'post_id' => 'required|exists:posts,id',
            'parent_comment_id' => 'nullable|exists:comments,id', 
        ]);
        //comment as text. a post, a user, and might have a parent comment if it is a reply, otherwise NULL
        $comment = Comment::create([
            'content' => $request->content,
            'post_id' => $request->post_id,
            'parent_comment_id' => $request->parent_comment_id, 
            'authenticated_user_id' => Auth::id(), 
        ]);
        
        return response()->json([
            'message' => 'Comment created successfully!',
            'comment' => $comment,
        ], 201);
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



