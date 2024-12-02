<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Post;

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

        $followers = $user->followers;
        $following = $user->follows;
        $posts = $user->authoredPosts()->paginate(10);

        return view('pages.profile', compact('user', 'followers', 'following', 'posts'));
    }

    public function getFollowers($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $followers = $user->followers;

        return view('pages.followers', compact('user', 'followers'));
    }

    public function getFollows($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $following = $user->follows;

        return view('pages.following', compact('user', 'following'));
    }
    /**
     * Update the specified user in storage.
     */
    public function edit($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        // Check if the logged-in user is trying to edit their own profile
        $this->authorize('editProfile', $user);
        if (Auth::user()->id != $id) {
            // If not, deny access by returning a 403 error or redirecting them
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If the logged-in user is editing their own profile, fetch the user
        $user = Auth::user();

        return view('pages.edit_profile', compact('user'));
    }

    public function update(Request $request, $id)
{

    if (Auth::user()->id != $id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $user = AuthenticatedUser::findOrFail($id);


    // Validate the data
    $validatedData = $request->validate([
        'name' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:255|unique:authenticated_users,username,' . $user->id,
        'email' => 'nullable|email|max:255|unique:authenticated_users,email,' . $user->id,
        'birth_date' => 'nullable|date|before:today',
        'description' => 'nullable|string',
        'password' => 'nullable|string|min:8|confirmed',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Update only provided fields
    if (!empty($validatedData['name'])) {
        $user->name = $validatedData['name'];
    }
    if (!empty($validatedData['username'])) {
        $user->username = $validatedData['username'];
    }
    if (!empty($validatedData['email'])) {
        $user->email = $validatedData['email'];
    }
    if (!empty($validatedData['birth_date'])) {
        $user->birth_date = $validatedData['birth_date'];
    }
    if (!empty($validatedData['description'])) {
        $user->description = $validatedData['description'];
    }

    // Update password if provided
    if (!empty($validatedData['password'])) {
        $user->password = Hash::make($validatedData['password']);
    }

    // Handle file upload if provided
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('images'), $filename);
        $user->image_id = $filename; // Save the filename as the image ID
    }

    $user->save();

    // Redirect back to the A page with success message
    return redirect()->route('user.profile', $user->id)->with('success', 'Profile updated successfully!');
}

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        if (Auth::user()->id != $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
     * Get the posts authored by the user and display them on the profile page.
     */
    public function getAuthoredPosts($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $followers = $user->followers;
        $following = $user->follows;

        // Fetch authored posts with pagination
        $posts = $user->authoredPosts()->paginate(10);

        return view('pages.profile', compact('user', 'followers', 'following', 'posts'));
    }

    public function suspend($id)
    {
        // Check if the current user is an admin
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
        // Check if the current user is an admin
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->is_suspended = false;
        $user->save();

        return response()->json(['message' => 'User unsuspended successfully']);
    }

    public function favorites() {

        if (!Auth::check()) {
            return response()->json(['message' => 'You are not logged in'], 403);
        }
    
        $favorites = Auth::user()->favouritePosts;
        return view('partials.favorites',compact('favorites'));
    }

    public function addfavorite(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'You are not logged in'], 403);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $user = Auth::user();

        if ($user->favouritePosts()->where('post_id', $id)->exists()) {
            return response()->json(['message' => 'Post is already in your favorites'], 400);
        }

        $user->favouritePosts()->attach($id);

        return response()->json(['message' => 'Post added to favorites successfully'], 201);
    }

    public function remfavorite(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'You are not logged in'], 403);
        }
    
        $post = Post::find($id);
    
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
    
        $user = Auth::user();
    
        if (!$user->favouritePosts()->where('post_id', $id)->exists()) {
            return response()->json(['message' => 'Post is not in your favorites'], 400);
        }
    
        $user->favouritePosts()->detach($id);
    
        return response()->json(['message' => 'Post removed from favorites successfully'], 200);
    }
    
}
