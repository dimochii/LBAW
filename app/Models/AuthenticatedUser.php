<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticatedUser extends Model
{
    use Notifiable, use HasFactory;
    protected $table = 'AuthenticatedUser';
    const CREATED_AT = 'creationDate';
    const UPDATED_AT = null;
    protected $fillable = [
        'name', 'username', 'email', 'password', 'reputation', 
        'isSuspended', 'creationDate', 'birthDate', 'description', 'isAdmin', 'image_id'
    ];

    public function image()
    {
        return $this->hasOne(Image::class);
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'CommunityFollower');
    }

    public function follows()
    {
        return $this->hasMany(AuthenticatedUser::class, 'UserFollower')
    }

    public function followers()
    {
        return $this->hasMany(AuthenticatedUser::class, 'UserFollower')
    }
    
    public function authoredPosts()
    {
        return $this->belongsToMany(Post::class, 'Author')
                    ->withPivot('pinned')
    }

    public function favouritePosts()
    {
        return $this->belongsToMany(Post::class, 'FavouritePost')
    }

    public function votes()
    {
        return $this->hasMany(Vote::class)
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)
    }

    public function suspensions()
    {
        return $this->hasMany(Supension::class)
    }

    public function reports()
    {
        return $this->hasMany(Report::class)
    }
    

}
