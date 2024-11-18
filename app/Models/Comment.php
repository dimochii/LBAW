<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    public $timestamps = true;
    const CREATED_AT = 'creation_date';
 

    protected $fillable = [
        'content', 'creation_date', 'updated', 
        'authenticated_user_id', 'post_id', 'parent_comment_id'
    ];


    

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class, 'authenticated_user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    public function Votes()
    {
        return $this->hasMany(CommentVote::class);
    }

    public function upvotesCount()
    {
        return $this->hasMany(CommentVote::class)
                    ->whereHas('vote', function ($query) {
                        $query->where('upvote', true);
                    });
    }

    public function downvotesCount()
    {
        return $this->hasMany(CommentVote::class)
                    ->whereHas('vote', function ($query) {
                        $query->where('upvote', false);
                    });
    }

    public function notification()
    {
        return $this->hasOne(CommentNotification::class);
    }
}
