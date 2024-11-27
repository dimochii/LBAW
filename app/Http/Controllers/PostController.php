<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\News;
use App\Models\Topic;

class PostController extends Controller
{
    public function createPost()
    {
        return view('pages.create_post');
    }
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'community_id' => 'required|exists:communities,id',
            'type' => 'required|in:news,topic',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'community_id' => $request->community_id,
        ]);

        $user = Auth::user(); 
        $post->authors()->attach($user->id, ['pinned' => false]); 


        if ($request->type === 'news') {
            return app(NewsController::class)->createNews($post, $request->news_url);
        } elseif ($request->type === 'topic') {
            return app(TopicController::class)->createTopic($post);
        }
        

        return response()->json(['message' => 'Invalid type'], 400);
    }

    //Gets a post, checks if the user who's trying to delete is the owner and erases it, if it has no comments or votes
    public function delete(Request $request, $id)
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

}