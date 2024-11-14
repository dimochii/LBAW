<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = null;
    protected $fillable = ['title', 'creation_date', 'content', 'community_id'];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function votes()
    {
        return $this->hasMany(PostVote::class);
    }

    public function news()
    {
        return $this->hasOne(News::class);
    }

    public function topic()
    {
        return $this->hasOne(Topic::class);
    }

    public function authors()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'authors')
                    ->withPivot('pinned');
    }

    public function favourites()
    {
        return $this->belongsToMany(AuthenticatedUser::class,'favourite_post');
    }

    public function postNotification()
    {
        return $this->hasOne(PostNotification::class);
    }
}
