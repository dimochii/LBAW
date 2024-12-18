<?php

namespace App\Http\Controllers;

use App\Models\AuthenticatedUser;
use App\Models\Post;
use App\Models\News;
use App\Models\Topic;
use App\Models\Image;
use App\Models\FollowNotification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedUserController extends Controller
{

    public function index()
    {
        $users = AuthenticatedUser::all();
        return response()->json($users);
    }


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


    public function show($id)
    {

        $user = AuthenticatedUser::findOrFail($id);
        $followers = $user->followers;
        $following = $user->follows;

        $authored_news = $this->getAuthoredNews($user);
        $authored_topics = $this->getAuthoredTopics($user);
        $voted_news = $this->getVotedNews($user);
        $voted_topics = $this->getVotedTopics($user);
        $favourite_news = $this->getFavouriteNews($user);
        $favourite_topics = $this->getFavouriteTopics($user);
        $reputation = $this->getReputation($user);

        
        if (!Auth::check()) {
            return response()->json(['message' => 'You are not logged in'], 403);
        }
    
        $favorites = Auth::user()->favouritePosts;

        $isFollowing = Auth::check() && Auth::user()->follows()->where('followed_id', $id)->exists();

        return view('pages.profile', compact(
            'user', 'followers', 'following', 'authored_news', 'favorites', 
            'authored_topics', 'voted_news', 'voted_topics','favourite_news', 'favourite_topics', 'isFollowing', 'reputation'
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

    public function edit($id)
    {
        $user = AuthenticatedUser::findOrFail($id);

        if (!$this->authorize('editProfile', $user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = Auth::user();

        return view('pages.edit_profile', compact('user'));
    }

    public function update(Request $request, $id)
{
    if (Auth::user()->id != $id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $user = AuthenticatedUser::findOrFail($id);

    $validatedData = $request->validate([
        'name' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:255|unique:authenticated_users,username,' . $user->id,
        'email' => 'nullable|email|max:255|unique:authenticated_users,email,' . $user->id,
        'birth_date' => 'nullable|date|before:today',
        'description' => 'nullable|string',
        'password' => 'nullable|string|min:8|confirmed',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


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

    if (!empty($validatedData['password'])) {
        $user->password = Hash::make($validatedData['password']);
    }

    // Handle image (or other file) update
    if ($request->hasFile('image')) {

        $file = $request->file('image');
        $newFileId = uniqid();
        $extension = $file->getClientOriginalExtension(); 
        $newFilename = 'user' . $newFileId . '.' . $extension;

        if ($user->image_id) {
            $existingImage = Image::find($user->image_id);
            if ($existingImage && file_exists(base_path('images/' . $existingImage->path))) {
                unlink(base_path('images/' . $existingImage->path)); 
            }
        }

        $file->move(base_path('images'), $newFilename);
        $image = Image::firstOrCreate(['path' => 'images/' . $newFilename]); 
        $user->image_id = $image->id;
    }
    $user->save();
    return redirect()->route('user.profile', $user->id)->with('success', 'Profile updated successfully!');
    }




    public function destroy($id)
    {
        if (Auth::user()->id != $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }


    public function getCommunities($id)
    {
        $user = AuthenticatedUser::findOrFail($id);
        $communities = $user->communities;

        return response()->json($communities);
    }


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


    public function getFavouriteNews($user)
    {
        return $this->fetchPostData($user->favouritePosts()->whereHas('news'));
    }

    public function getFavouriteTopics($user)
    {
        return $this->fetchPostData($user->favouritePosts()->whereHas('topic'));
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
    
            if ($authenticatedUser->follows()->where('followed_id', $userToFollow->id)->exists()) {
                $authenticatedUser->follows()->detach($userToFollow->id);
    
                return redirect()->back()->with('success', 'You have unfollowed ' . $userToFollow->name);
            } else {
                $authenticatedUser->follows()->attach($userToFollow->id);
    
                return redirect()->back()->with('success', 'You are now following ' . $userToFollow->name);
            }
        }
    
        return redirect()->back()->with('error', 'Something went wrong.');
    }
    

    public function  makeAdmin($id)
    {
        
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->is_admin = true;
        $user->save();

        return response()->json(['message' => 'User gained admin privileges successfully']);
    }
    public function  removeAdmin($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->is_admin = false;
        $user->save();

        return response()->json(['message' => 'User lost admin privileges successfully']);
    }
    

    public function suspend($id)
    { 
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = AuthenticatedUser::findOrFail($id);
        $user->is_suspended = true;
        $user->save();

        return response()->json(['message' => 'User suspended successfully']);
    }

    public function unsuspend($id)
    {
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
        
        //Update votes --> deleted user
        if ($user->votes()->exists()) {
            $user->votes()->update(['authenticated_user_id' => $deletedUserId]);
        }
        //Update comments --> deleted user
        if ($user->comments()->exists()) {
            $user->comments()->update(['authenticated_user_id' => $deletedUserId]);
        }
        //update post ---> solo writer --> deleted user// co-author ---> just remove
        foreach ($user->authoredPosts as $post) {
            $authorCount = $post->authors()->count();
            if ($authorCount === 1) {
                $post->update(['authenticated_user_id' => $deletedUserId]);
                $post->authors()->syncWithoutDetaching([$deletedUserId]); 
                $post->authors()->detach($user->id); 
            } 

            else {
                $post->authors()->detach($user); 
                //$post->authors()->attach($deletedUser); // Add the deleted user as an author
            }
        }

        //delete user notifications....
        if ($user->notifications()->exists()) {
            $user->notifications()->update(['authenticated_user_id' => $deletedUserId]);
        }
    
        //erase reports with user.....
        if ($user->reports()->exists()) {
            $user->reports()->delete(); 
        }
        //erase suspensions with user...
        if ($user->suspensions()->exists()) {
            $user->suspensions()->delete(); 
        }


        FollowNotification::where('follower_id', $user->id)
        ->update(['follower_id' => $deletedUserId]);

        
        $user->moderatedCommunities()->detach();
        $user->favouritePosts()->detach();
        $user->communities()->detach();
        $user->follows()->detach();
        $user->followers()->detach();
        $user->delete();
        Auth::logout();
    
        return redirect('/global')->with('message', 'Your account has been successfully deleted.');
    }
    
    
    
    public function deleteUserAccount(Request $request, $id) {
        $admin = Auth::user();
        
        // Check if the authenticated user is an admin
        if (!$admin->is_admin) {
            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        }
    
        $user = AuthenticatedUser::find($id);
        $deletedUserId = 1;
        $deletedUser = AuthenticatedUser::find($deletedUserId);

        //Update votes --> deleted user
        if ($user->votes()->exists()) {
            $user->votes()->update(['authenticated_user_id' => $deletedUserId]);
        }

        //Update comments --> deleted user
        if ($user->comments()->exists()) {
            $user->comments()->update(['authenticated_user_id' => $deletedUserId]);
        }

        //update post ---> solo writer --> deleted user// co-author ---> just remove
        foreach ($user->authoredPosts as $post) {
            $authorCount = $post->authors()->count();
            if ($authorCount === 1) {
                $post->update(['authenticated_user_id' => $deletedUserId]);
                $post->authors()->syncWithoutDetaching([$deletedUserId]); 
                $post->authors()->detach($user->id); 
            } 

            else {
                $post->authors()->detach($user); 
                //$post->authors()->attach($deletedUser); // Add the deleted user as an author
            }
        }
     

        //delete user notifications....
        if ($user->notifications()->exists()) {
            $user->notifications()->update(['authenticated_user_id' => $deletedUserId]);
        }
    
        //erase reports with user.....
        if ($user->reports()->exists()) {
            $user->reports()->delete(); 
        }
        //erase suspensions with user...
        if ($user->suspensions()->exists()) {
            $user->suspensions()->delete(); 
        }

        FollowNotification::where('follower_id', $user->id)
        ->update(['follower_id' => $deletedUserId]);

        
        $user->moderatedCommunities()->detach();
        $user->favouritePosts()->detach();
        $user->communities()->detach();
        $user->follows()->detach();
        $user->followers()->detach();
        $user->delete();
    
        return redirect()->route('admin.users')->with('message', 'User account has been successfully deleted.');
        
    }
    
    public function getReputation($user)
    {

        $postVotes = $user->authoredPosts()
            ->with('votes') // Load votes relation
            ->get()
            ->flatMap(function ($post) {
                return $post->votes;
            });
        
        $postUpvotes = $postVotes->where('upvote', true)->count();
        $postDownvotes = $postVotes->where('upvote', false)->count();

        // Calculate comment upvotes and downvotes
        $commentVotes = $user->comments()
            ->with('votes') // Load votes relation
            ->get()
            ->flatMap(function ($comment) {
                return $comment->votes;
            });

        $commentUpvotes = $commentVotes->where('vote.upvote', true)->count();
        $commentDownvotes = $commentVotes->where('vote.upvote', false)->count();

        // Calculate the net score
        $postNetScore = $postUpvotes - $postDownvotes;
        $commentNetScore = $commentUpvotes - $commentDownvotes;

        $reputation = $postNetScore + $commentNetScore;

        // Return the result
        return $reputation;
    }
    

}
