<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = null;
    protected $fillable = ['title', 'content', 'community_id'];

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
        return $this->hasManyThrough(Vote::class, PostVote::class, 'post_id', 'id', 'id', 'vote_id');
    }

    public function getUpvoteCountAttribute()
    {
        return $this->votes()->where('upvote', true)->count();
    }

    public function getDownvoteCountAttribute()
    {
        return $this->votes()->where('upvote', false)->count();
    }

    public function getScoreAttribute()
    {
        return $this->upvote_count - $this->downvote_count;
    }

    public function userVote($userId)
    {
        return $this->votes()->where('authenticated_user_id', $userId)->first();
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
        return $this->belongsToMany(AuthenticatedUser::class,'favorite_posts');
    }

    public function postNotification()
    {
        return $this->hasOne(PostNotification::class);
    }
    public function getCreatedAtAttribute()
    {
    return $this->attributes['creation_date'];
    }
}
