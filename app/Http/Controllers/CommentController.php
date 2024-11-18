<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;


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


}
