<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Post;
use App\Models\Community;

class AuthenticatedUserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function editProfile(AuthenticatedUser $user, AuthenticatedUser $user_profile): bool
    {
        return ($user->id == $user_profile->id);
    }

    public function isAuthor(AuthenticatedUser $user, Post $post): bool
    {
        return $post->authors->contains($user->id);
    }

    public function isAdmin(AuthenticatedUser $user): bool
    {
        return $user->is_admin === true; 
    }

    public function isCommunityAdmin(AuthenticatedUser $user, Community $community): bool
    {
        //return $community->moderators->contains($user->id);
        return $user->moderatedCommunities->contains($community);
    }
}
