<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Post;
use App\Models\Vote;
use App\Models\PostVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Display a listing of all news articles.
     */
    public function list()
    {
        $news = News::with('post')->get();
    
        foreach ($news as $item) {
            $item->upvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
                $query->where('post_id', $item->post_id);
            })->where('upvote', true)->count();
    
            $item->downvotes_count = Vote::whereHas('postVote', function ($query) use ($item) {
                $query->where('post_id', $item->post_id);
            })->where('upvote', false)->count();
        }
    
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

        $newsItem->post->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $newsItem->update([
            'news_url' => $request->news_url,
        ]);

        return view('pages.show_news', compact('newsItem'));
    }



    public function upvote($post_id)
    {
        $post = Post::findOrFail($post_id);
        $user = Auth::user();

        $existingVote = $post->votes()->where('authenticated_user_id', $user->id)->first();

        if ($existingVote) {
            if ($existingVote->upvote) {
                return redirect()->back()->with('success', 'You have already upvoted this post.');
            }

            $existingVote->update(['upvote' => true]);
        } else {
            $vote = Vote::create(['upvote' => true, 'authenticated_user_id' => $user->id]);
            PostVote::insert([
                'vote_id' => $vote->id,
                'post_id' => $post->id,
            ]);        }

        return redirect()->back()->with('success', 'Post upvoted successfully.');
    }

    public function downvote($post_id)
    {
        $post = Post::findOrFail($post_id);
        $user = Auth::user();

        $existingVote = $post->votes()->where('authenticated_user_id', $user->id)->first();

        if ($existingVote) {
            if (!$existingVote->upvote) {
                return redirect()->back()->with('success', 'You have already downvoted this post.');
            }
            $existingVote->update(['upvote' => false]);
        } else {
            $vote = Vote::create(['upvote' => false, 'authenticated_user_id' => $user->id]);
            PostVote::insert([
                'vote_id' => $vote->id,
                'post_id' => $post->id,
            ]);        }

        return redirect()->back()->with('success', 'Post downvoted successfully.');
    }

    
}
