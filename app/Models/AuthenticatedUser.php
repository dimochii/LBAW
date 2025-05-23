<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthenticatedUser extends Authenticatable
{
    use HasFactory;
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = null;
    protected $fillable = [
        'name', 'username', 'email', 'password', 'reputation', 
        'is_suspended', 'creation_date', 'birth_date', 'description', 'is_admin', 'image_id', 'google_id'
    ];

    public function getAuthIdentifierName()
    {
        return 'email';
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
    public function username()
    {
        return $this->username;
    }


    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id', 'id');  
    }


    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'community_followers');
    }

    public function moderatedCommunities()
    {
        return $this->belongsToMany(Community::class, 'community_moderators');
    }

    public function follows()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'user_followers', 'follower_id', 'followed_id');
    }
    
    public function followers()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'user_followers', 'followed_id', 'follower_id');
    }
    
    public function authoredPosts()
    {
        return $this->belongsToMany(Post::class, 'authors');
    }

    public function favouritePosts()
    {
        return $this->belongsToMany(Post::class, 'favorite_posts');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function suspensions()
    {
        return $this->hasMany(Suspension::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function followUserNotification()
    {
        return $this->hasOne(FollowNotification::class);
    }
    
    
}
