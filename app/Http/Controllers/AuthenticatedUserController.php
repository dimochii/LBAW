<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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
    
        $followers = $user->followers;
        $following = $user->follows;
    
        // Get posts authored by the user
        $posts = $user->authoredPosts()->paginate(10);
    
        // Get the currently authenticated user
        $authUser = Auth::user();
    
        foreach ($posts as $post) {
            // Get upvote and downvote counts
            $post->upvotes_count = $post->upvoteCount();
            $post->downvotes_count = $post->downvoteCount();
            $post->score = $post->upvotes_count - $post->downvotes_count;
    
            // Check if the authenticated user has voted on this post
            $userVote = $authUser->votes()
                ->whereHas('postVote', function ($query) use ($post) {
                    $query->where('post_id', $post->id);
                })
                ->first();
    
            // Determine if the user has upvoted or downvoted the post
            if ($userVote) {
                $post->user_upvoted = $userVote->upvote;
                $post->user_downvoted = !$userVote->upvote;  // If it's not an upvote, it's a downvote
            } else {
                // User has not voted on this post
                $post->user_upvoted = false;
                $post->user_downvoted = false;
            }
        }
    
        // Return the profile view with the user, followers, following, and posts
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

        return view('pages.edit_profile', compact('user'));
    }

    public function update(Request $request, $id)
{
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

    // Redirect back to the profile page with success message
    return redirect()->route('user.profile', $user->id)->with('success', 'Profile updated successfully!');
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
