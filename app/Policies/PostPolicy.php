<?php

namespace App\Policies;

use App\Models\AuthenticatedUser;
use App\Models\Post;

class PostPolicy
{

    public function isAuthor(AuthenticatedUser $user, Post $post): bool
    {
        return $post->authors->contains('id', $user->id);
    }
}
