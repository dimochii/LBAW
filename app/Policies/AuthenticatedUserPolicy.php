<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;

class AuthenticatedUserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function editProfile(AuthenticatedUser $user, AuthenticatedUser $user_profile): bool
    {
        return ($user->id == $user_profile->id);
    }
}
