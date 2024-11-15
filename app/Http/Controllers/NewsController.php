<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Display a listing of all news articles.
     */
    public function list()
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            // Redirect to login page if not logged in
            return redirect('/login');
        }

        // Get all news articles from the database
        $news = News::with('post')->orderBy('post_id', 'desc')->get();

        // Return the view with the list of news
        return view('pages.news', [
            'news' => $news
        ]);
    }
}
