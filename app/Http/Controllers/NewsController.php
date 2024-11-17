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
    public function show($post_id)
    {
        $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();
        
        return view('pages.show_news', compact('newsItem'));
    }
    
    public function createNews(Post $post, $newsUrl)
    {
        $news = News::create([
            'post_id' => $post->id,
            'news_url' => $newsUrl,
        ]);

        return redirect()->route('news')->with('success', 'News created successfully');
    }

    public function edit($post_id)
    {
        $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();

        // Ensure the current user is an author of the post
        if (!$newsItem->post->authors->contains('id', Auth::user()->id)) {
            abort(403, 'Unauthorized');
        }

        return view('pages.edit_news', compact('newsItem'));
    }

    public function update(Request $request, $post_id)
    {
        $newsItem = News::with('post')->where('post_id', $post_id)->firstOrFail();

        // Ensure the current user is an author of the post
        if (!$newsItem->post->authors->contains('id', Auth::user()->id)) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'news_url' => 'required|url',
        ]);

        // Update the post
        $newsItem->post->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // Update the news URL
        $newsItem->update([
            'news_url' => $request->news_url,
        ]);

        return view('pages.show_news', compact('newsItem'));
        //return redirect()->route('news.edit', $post_id)->with('success', 'News updated successfully');
    }
    
}
