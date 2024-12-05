<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUser;
use App\Models\Post;
use App\Models\News;
use App\Models\Topic;
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

        $authored_news = $this->getAuthoredNews($user);
        $authored_topics = $this->getAuthoredTopics($user);
        $voted_news = $this->getVotedNews($user);
        $voted_topics = $this->getVotedTopics($user);
        
        if (!Auth::check()) {
            return response()->json(['message' => 'You are not logged in'], 403);
        }
    
        $favorites = Auth::user()->favouritePosts;

        $isFollowing = Auth::check() && Auth::user()->follows()->where('followed_id', $id)->exists();

        return view('pages.profile', compact(
            'user', 'followers', 'following', 'authored_news', 'favorites', 
            'authored_topics', 'voted_news', 'voted_topics', 'isFollowing'
        ));
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
    private function fetchPostData($query)
    {
        $posts = $query->withCount([
            'votes as upvotes_count' => fn($q) => $q->where('upvote', true),
            'votes as downvotes_count' => fn($q) => $q->where('upvote', false),
            'comments as comments_count'
        ])->get();
    
        foreach ($posts as $post) {
            $post->user_upvoted = Auth::check() ? $post->userVote(Auth::user()->id)?->upvote ?? false : false;
            $post->user_downvoted = Auth::check() ? !$post->userVote(Auth::user()->id)?->upvote ?? false : false;
        }
    
        return $posts;
    }

    public function getAuthoredNews($user)
    {
        return $this->fetchPostData($user->authoredPosts()->whereHas('news'));
    }

    public function getAuthoredTopics($user)
    {
        return $this->fetchPostData($user->authoredPosts()->whereHas('topic'));
    }  

    public function getVotedNews($user)
    {
        return $this->fetchPostData(Post::whereHas('news')->whereHas('votes', function ($query) use ($user) {
            $query->where('authenticated_user_id', $user->id)->where('upvote', true);
        }));
    }

    
    public function getVotedTopics($user)
    {
        return $this->fetchPostData(Post::whereHas('topic')->whereHas('votes', function ($query) use ($user) {
            $query->where('authenticated_user_id', $user->id)->where('upvote', true);
        }));
    }

    public function follow($id)
    {
        $userToFollow = AuthenticatedUser::findOrFail($id);
        
        if (Auth::check()) {
            $authenticatedUser = Auth::user(); 
    
            // Check if the authenticated user is already following the target user
            if ($authenticatedUser->follows()->where('followed_id', $userToFollow->id)->exists()) {
                // If already following, detach (unfollow)
                $authenticatedUser->follows()->detach($userToFollow->id);
    
                return redirect()->back()->with('success', 'You have unfollowed ' . $userToFollow->name);
            } else {
                // If not following, attach (follow)
                $authenticatedUser->follows()->attach($userToFollow->id);
    
                return redirect()->back()->with('success', 'You are now following ' . $userToFollow->name);
            }
        }
    
        return redirect()->back()->with('error', 'Something went wrong.');
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
    //Deleted User ---> id = 1

    public function deletemyaccount() {
        $user = Auth::user();
        $deletedUserId = 1;     
        $deletedUser = AuthenticatedUser::find($deletedUserId);
    
        if (!$deletedUser) {
            return redirect('/news')->with('error', 'Unable to delete account: Deleted user does not exist.');
        }
        
        foreach ($user->votes ?? [] as $vote) {
            $vote->authenticated_user_id = $deletedUserId;
            $vote->save();
        }
    
        foreach ($user->comments ?? [] as $comment) {
            $comment->authenticated_user_id = $deletedUserId;
            $comment->save();
        }
    
        foreach ($user->posts ?? [] as $post) {
            $post->authenticated_user_id = $deletedUserId;
            $post->save();
        }

        foreach ($user->notifications ?? [] as $notification) {
            $notification->authenticated_user_id = $deletedUserId;
            $notification->save();
        }

        $user->favouritePosts()->detach();
        $user->communities()->detach();
        $user->follows()->detach();
        $user->followers()->detach();
    
        $user->delete();
        Auth::logout();
    
        return redirect('/news')->with('message', 'Your account has been successfully deleted.');
    }
    
    public function deleteUserAccount(Request $request, $id) {
        $admin = Auth::user();
        
        // Check if the authenticated user is an admin
        if (!$admin->is_admin) {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    
        $user = AuthenticatedUser::find($id);
        
        // Check if the user exists
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
    
        $deletedUserId = 1; // ID of the "deleted user"
        $deletedUser = AuthenticatedUser::find($deletedUserId);
        
        // Check if the "deleted user" exists
        if (!$deletedUser) {
            return redirect('/news')->with('error', 'Unable to delete account: Deleted user does not exist.');
        }
    
        // Reassign votes
        foreach ($user->votes ?? [] as $vote) {
            $vote->authenticated_user_id = $deletedUserId;
            $vote->save();
        }
    
        // Reassign comments
        foreach ($user->comments ?? [] as $comment) {
            $comment->authenticated_user_id = $deletedUserId;
            $comment->save();
        }
    
        // Reassign posts
        foreach ($user->authoredPosts ?? [] as $post) {
            $post->pivot->authenticated_user_id = $deletedUserId;
            $post->pivot->save();
        }
    
        // Reassign notifications
        foreach ($user->notifications ?? [] as $notification) {
            $notification->authenticated_user_id = $deletedUserId;
            $notification->save();
        }
    
        // Detach relationships
        $user->favouritePosts()->detach();
        $user->communities()->detach();
        $user->follows()->detach();
        $user->followers()->detach();
    
        // Delete the user
        $user->delete();
    
        return redirect('/news')->with('message', 'User account has been successfully deleted.');
    }
    

}
