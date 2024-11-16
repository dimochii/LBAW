<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Post;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function createTopic(Post $post)
    {
        $topic = Topic::create([
            'post_id' => $post->id,
        ]);

        return redirect()->route('news')->with('success', 'Topic created successfully');

    }
}
