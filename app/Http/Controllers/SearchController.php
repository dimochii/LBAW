<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\Post;
use App\Models\AuthenticatedUser;

class SearchController extends Controller
{
    public function search(Request $request)  //get com os 3 tipos de pesquisa: users, posts, comunidades
    {
        $search = $request->input('search');

        // Validate the input
        $request->validate([
            'search' => 'required|string|max:255',
        ]);

        // Search communities
        $communities = Community::where('name', 'like', "%$search%")
                                ->orWhere('description','like',"%$search%")
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
        $posts = Post::whereRaw("to_tsvector('english', title || ' ' || content) @@ plainto_tsquery(?)", [$search])  
             ->get()
             -> map(function($post) {
                return [
                    'name' => $post->title,
                    'content' => $post->content,
                    'community' => $post->community->name,
                    'community_route' => url("/community/{$post->community->id}"),

                ];
             });


        // Search users -> return name,photo and route 
        $users = AuthenticatedUser::where('name', 'like', "%$search%")
                     ->orWhere('email', 'like', "%$search%")
                     ->get()
                     ->map(function($user) {
                        return [
                            'name' => $user->name,
                            'image' => $user->image_id,
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

}
