<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'content', 'creation_date', 'updated', 
        'authenticated_user_id', 'post_id', 'parent_comment_id'
    ];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class);
    }

    public function children()
    {
        return $this->hasMany(Comment::class);
    }

    public function Votes()
    {
        return $this->hasMany(CommentVote::class);
    }

    public function notification()
    {
        return $this->hasOne(CommentNotification::class);
    }
}
