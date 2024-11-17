<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticatedUserController extends Controller
{
      /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = AuthenticatedUser::all();
        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:authenticated_users',
            'email' => 'required|string|email|max:255|unique:authenticated_users',
            'password' => 'required|string|min:8',
            'birth_date' => 'required|date|before:today',
            'description' => 'nullable|string',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);
        
        $user = AuthenticatedUser::create($validatedData);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        return view('pages.profile', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = AuthenticatedUser::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:authenticated_users,username,' . $user->id,
            'email' => 'sometimes|string|email|max:255|unique:authenticated_users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'birth_date' => 'sometimes|date|before:today',
            'description' => 'nullable|string',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get the communities followed by the user.
     */
    public function getCommunities($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $communities = $user->communities;

        return response()->json($communities);
    }

    /**
     * Get the posts authored by the user.
     */
    public function getAuthoredPosts($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $posts = $user->authoredPosts;

        return response()->json($posts);
    }

    /**
     * Get the user's followers.
     */
    public function getFollowers($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $followers = $user->followers;

        return response()->json($followers);
    }

    /**
     * Get the users that the user follows.
     */
    public function getFollows($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $follows = $user->follows;

        return response()->json($follows);
    }

    /**
     * Suspend a user.
     */
    public function suspend($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $user->is_suspended = true;
        $user->save();

        return response()->json(['message' => 'User suspended successfully']);
    }

    /**
     * Unsuspend a user.
     */
    public function unsuspend($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $user->is_suspended = false;
        $user->save();

        return response()->json(['message' => 'User unsuspended successfully']);
    }
}
