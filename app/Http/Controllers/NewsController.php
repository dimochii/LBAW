<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Display a listing of all news articles.
     */
    public function list()
    {
        $news = News::with('post')->orderBy('post_id', 'desc')->get();

        return view('pages.news', [
            'news' => $news
        ]);
    }
    public function createNews(Post $post, $newsUrl)
    {
        $news = News::create([
            'post_id' => $post->id,
            'news_url' => $newsUrl,
        ]);

        return redirect()->route('news')->with('success', 'News created successfully');
    }
}
