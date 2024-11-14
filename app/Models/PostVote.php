<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostVote extends Vote
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['vote_id', 'post_id'];
    protected $primaryKey = ['vote_id', 'post_id'];

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
