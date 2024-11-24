<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{


    public function show($id)
    {
        $community = Community::with('posts')->find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        // Transform the posts data to return relevant fields
        $posts = $community->posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'author' => $post->authors->map(fn ($author) => $author->name)->join(', '), // Assuming many authors
                'created_at' => $post->created_at,
                'route' => $post->id
            ];
        });

        $moderators = $community->moderators->map(function ($moderator) {
            return [
                'id' => $moderator->id,
                'username' => $moderator->name
            ];
        });

    $result = [
        'id' => $community->id,
        'name' => $community->name,
        'description' => $community->description,
        'followers' => $community->followers->count(),
        'privacy' => $community->privacy,
        'creation_date' => $community->creation_date,
        'image' => $community->image_id,
        'moderators' => $moderators,
        'posts' => $posts,
    ];

    return response()->json($result);
}



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:communities',
            'description' => 'required|string|max:1000',
            'privacy' => 'required|in:public,private',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $community = Community::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy' => $request->privacy,
            'image_id' => $request->image_id,
            'creation_date' => now(),
        ]);

        $authUser = Auth::user();
        $community->moderators()->attach($authUser->id);

        return response()->json([
            'message' => 'Community created successfully',
            'community' => $community,
        ], 201);
    }


    public function join(Request $request, $id)
{
    $community = Community::find($id);

    if (!$community) {
        return response()->json(['message' => 'Community not found'], 404);
    }

    if ($community->privacy === 'private') {
        return response()->json(['message' => 'You cannot directly join a private community'], 403);
    }

    $authUser = Auth::user();
    $alreadyJoined = $community->followers()->where('user_id', $authUser->id)->exists();

    if ($alreadyJoined) {
        return response()->json(['message' => 'You are already a member of this community'], 400);
    }

    $community->followers()->attach($authUser->id);

    return response()->json(['message' => 'You have successfully joined the community']);
}

    /*
    public function apply(Request $request, $id)
    {
        $community = Community::find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        if ($community->privacy !== 'private') {
            return response()->json(['message' => 'You cannot apply to a public community'], 400);
        }

        $authUser = Auth::user();
        $alreadyApplied = $community->followers()->where('user_id', $authUser->id)->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'You have already applied or joined this community'], 400);
        }

        // Assuming there is a `pending_requests` table to handle applications
        $community->followers()->attach($authUser->id, ['status' => 'pending']);

        return response()->json(['message' => 'Your application to join the community has been submitted']);
    }*/
}
