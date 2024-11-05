<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentVote extends Vote
{
    use HasFactory;
    protected $table = 'CommentVote';
    public $timestamps = false;
    protected $fillable = ['vote_id', 'comment_id'];
    protected $primaryKey = ['vote_id', 'comment_id'];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
