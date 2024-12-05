<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthenticatedUser;
use App\Models\Community;
use App\Models\News;
use App\Models\Topic;
use App\Models\Post;

class AdminController extends Controller
{
    public function show($id)
    {
        $users = AuthenticatedUser::all();
        $hubs = Community::all();
        $posts = Post::all();
        $news = News::all();
        $topics = Topic::all();

        return view('pages.admin', compact(
            'users', 'hubs', 'posts', 'news', 'topics'
        ));
    }
}
