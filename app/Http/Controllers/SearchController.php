<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\Post;
use App\Models\News;
use App\Models\Topic;
use App\Models\AuthenticatedUser;

class SearchController extends Controller
{
    public function search(Request $request)  
{
    $search = $request->input('search');

    // Validate the input
    $request->validate([
        'search' => 'required|string|max:255',
    ]);

    // Make the search term lowercase for consistency
    $search = strtolower($search);

    // Search communities
    $communities = Community::whereRaw("LOWER(name) LIKE ?", ["%$search%"])
                            ->orWhereRaw("LOWER(description) LIKE ?", ["%$search%"])
                            ->get()
                            ->map(function($community) {
                                return [
                                    'name' => $community->name,
                                    'description' => $community->description,
                                    'image' => $community->image_id,
                                    'route' => url("/hub/{$community->id}"),
                                ];
                            });

    // Search posts
    $posts = Post::whereRaw(
        "to_tsvector('english', LOWER(title) || ' ' || LOWER(content)) @@ plainto_tsquery(?)", 
        [$search]
    )
    ->orWhereRaw("LOWER(title) LIKE ?", ["%$search%"])
    ->get()
    ->map(function ($post) {

        $topicExists = Topic::where('post_id', $post->id)->exists();
        return [
            'name' => $post->title,
            'content' => $post->content,
            'community' => $post->community->name,
            'community_route' => $topicExists 
                ? url("/topic/{$post->id}") 
                : url("/news/{$post->id}"),            
        ];
    });
    
    // Search users -> return name, photo, and route
    $users = AuthenticatedUser::whereRaw("LOWER(name) LIKE ?", ["%$search%"])
                              ->orWhereRaw("LOWER(email) LIKE ?", ["%$search%"])
                              ->get()
                              ->map(function($user) {
                                  return [
                                      'name' => $user->name,
                                      'image' => $user->image_id ? asset($user->image->path) : null,
                                      'route' => url("/users/{$user->id}/profile"),
                                  ];
                              });

    // Combine the results
    $results = [
        'communities' => $communities,
        'posts' => $posts,
        'users' => $users,
    ];

    return response()->json($results);
}


    public function searchUsers(Request $request)
    {
        $search = $request->input('search');

        $users = AuthenticatedUser::where('name', 'like', "%$search%")
            ->orWhere('email', 'like', "%$search%")
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $user->image_id ? asset("storage/{$user->image_id}") : asset('images/default-avatar.png'),
                ];
            });

        return response()->json($users);
    }


}
