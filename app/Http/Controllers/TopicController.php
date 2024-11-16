<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function createTopic(Post $post)
    {
        $topic = Topic::create([
            'post_id' => $post->id,
        ]);

        return redirect()->route('news')->with('success', 'News created successfully');

    }
}
