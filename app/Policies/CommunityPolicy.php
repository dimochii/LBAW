<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Community;

class CommunityPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function updatePrivacy(AuthenticatedUser $user, Community $community)
    {
        return $community->moderators->pluck('id')->contains($user->id);
    }
}
