<?php

namespace App\Http\Controllers;


use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorController extends Controller
{
    public function show(Request $request)
    { 

        $moderated_hubs= Auth::user()->moderatedCommunities;

        $selected_hub = null;

        if ($request->has('hub_id')) {
            $selected_hub = $moderated_hubs->firstWhere('id', $request->hub_id);
    
            if ($selected_hub) {
                $selected_hub->load(['posts', 'moderators', 'followers']);
            }
        }

        return view('pages.moderator', compact(
         'moderated_hubs','selected_hub'
        ));
    }


    public function  makeModerator($user_id, $community_id)
    {

        $community = Community::find($community_id);
        $userToAdd = AuthenticatedUser::find($user_id);

        if (!Auth::user()->moderatedCommunities->contains($community) && !Auth::user()->is_admin) {
            return response()->json(['error' => 'You do not have permission to add a moderator to this community.'], 403);
        }
        if ($community->moderators->contains($userToAdd)) {
            return response()->json(['error' => 'This user is already a moderator.'], 400);
        }

        $community->moderators()->attach($userToAdd);

        return response()->json(['message' => 'User gained admin privileges successfully']);
    }
    
    public function removeModerator($user_id, $community_id)
    {
        $community = Community::find($community_id);
        $userToRemove = AuthenticatedUser::find($user_id);

        if (!Auth::user()->moderatedCommunities->contains($community) && !Auth::user()->is_admin) {
            return response()->json(['error' => 'You do not have permission to remove a moderator from this community.'], 403);
        }

        if (!$community->moderators->contains($userToRemove)) {
            return response()->json(['error' => 'This user is not a moderator of the community.'], 400);
        }

        $community->moderators()->detach($userToRemove);

        return response()->json(['message' => 'User has been removed as a moderator successfully.']);
    }

    public function removeFollower($community_id, $user_id)
    {
        $community = Community::findOrFail($community_id);
        $user = AuthenticatedUser::findOrFail($user_id);

        if (!Auth::user()->moderatedCommunities->contains($community) && !Auth::user()->is_admin) {
            return response()->json(['error' => 'You do not have permission to remove followers from this community.'], 403);
        }

        if (!$community->followers->contains($user)) {
            return response()->json(['error' => 'This user is not a follower of the community.'], 400);
        }

        $community->followers()->detach($user);

        return response()->json(['message' => 'Follower successfully removed from the community.']);
    }
}
