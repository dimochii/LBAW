<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AuthenticatedUser extends Model
{
    use HasFactory;
    protected $table = 'authenticateduser';
    const CREATED_AT = 'creationdate';
    const UPDATED_AT = null;
    protected $fillable = [
        'name', 'username', 'email', 'password', 'reputation', 
        'issuspended', 'creationdate', 'birthdate', 'description', 'isadmin', 'image_id'
    ];

    public function image()
    {
        return $this->hasOne(Image::class);
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'communityfollower');
    }

    public function follows()
    {
        return $this->hasMany(AuthenticatedUser::class, 'userfollower');
    }

    public function followers()
    {
        return $this->hasMany(AuthenticatedUser::class, 'userfollower');
    }
    
    public function authoredPosts()
    {
        return $this->belongsToMany(Post::class, 'author')
                    ->withPivot('pinned');
    }

    public function favouritePosts()
    {
        return $this->belongsToMany(Post::class, 'favouritepost');
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
        return $this->hasMany(Supension::class);
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
