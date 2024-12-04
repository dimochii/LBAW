<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggleFavorite(Request $request, $postId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $post = Post::findOrFail($postId);

            if ($user->favorites()->where('post_id', $postId)->exists()) {
                $user->favorites()->detach($postId);
                return response()->json(['favorited' => false]);
            } else {
                $user->favorites()->attach($postId);
                return response()->json(['favorited' => true]);
            }
        } catch (\Exception $e) {
            \Log::error('Error toggling favorite: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
