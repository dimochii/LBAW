<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    // Fetch posts from user's communities created in the last 72 hours, order them by vote quantity, caches values for 60mins
    public function getHomePosts(Request $request) {

        $cachedPosts = Cache::get('user_posts');
    
        if ($cachedPosts) {
            return response()->json($cachedPosts);
        }

        $user = $request->user();

        
        $posts = Post::withCount('votes')
                    ->whereIn('community_id', $user->communities->pluck('id'))
                    ->where('creation_date', '>', now()->subHours(72)) 
                    ->orderBy('votes_count', 'desc') 
                    ->paginate(5); 

        
        $posts->getCollection()->transform(function($post) {
            return [
                'title' => $post->title,
                'content' => $post->content,
                'engagement' => $post->votes_count,
                'post_route' => $post->id,
                'community_route' => $post->community_id,
                'date' => $post->creation_date,
            ];
        });

        
        return response()->json([
            'data' => $posts->items(),
            'next_page_url' => $posts->nextPageUrl(), //supostamente esta é a estrutura que tenho de passar
        ]);
    }
    
    // Fetch posts from all public communities created in the last 72 hours, order them by vote quantity, caches values for 60minutes
    public function getGlobalPosts() {

        $cachedPosts = Cache::get('popular_posts');
    
        if ($cachedPosts) {
            return response()->json($cachedPosts);
        }
    
        $posts = Post::whereHas('community', function ($query) {
                        $query->where('privacy', false); 
                    })
                    ->where('creation_date', '>', now()->subHours(72)) 
                    ->withCount('votes') 
                    ->orderBy('votes_count', 'desc') 
                    ->paginate(5); 
    
        $posts->getCollection()->transform(function($post) {
            return [
                'title' => $post->title,
                'content' => $post->content,
                'engagement' => $post->votes_count,
                'post_route' => $post->id,
                'community_route' => $post->community_id,
                'creation' => $post->creation_date,
            ];
        });
    
        Cache::put('popular_posts', $posts, 60); 
    
        return response()->json([
            'data' => $posts->items(),
            'next_page_url' => $posts->nextPageUrl(), // For infinite scroll
        ]);
    }
        
    // Fetch posts from user's communities, sorts them by creation_date
    public function getRecentPosts(Request $request) {

        $user = $request->user();
        
        $posts = Post::withCount('votes')
                    ->whereIn('community_id', $user->communities->pluck('id')) 
                    ->orderBy('creation_date', 'desc') 
                    ->paginate(5); 
    
        $posts->getCollection()->transform(function($post) {
            return [
                'title' => $post->title,
                'content' => $post->content,
                'engagement' => $post->votes_count,
                'post_route' => $post->id,
                'community_route' => $post->community_id,
                'date' => $post->creation_date, 
            ];
        });
    
        return response()->json([
            'data' => $posts->items(),
            'next_page_url' => $posts->nextPageUrl(), // For infinite scroll
        ]);
    }
}
