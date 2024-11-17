<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            'community_id' => 'nullable|exists:communities,id',
            'type' => 'required|in:news,topic',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'community_id' => $request->community_id,
        ]);


        if ($request->type === 'news') {
            return app(NewsController::class)->createNews($post, $request->news_url);
        } elseif ($request->type === 'topic') {
            return app(TopicController::class)->createTopic($post);
        }
        

        return response()->json(['message' => 'Invalid type'], 400);
    }
}