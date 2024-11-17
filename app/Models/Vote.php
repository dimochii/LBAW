<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['upvote', 'authenticated_user_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }

    public function voteNotification()
    {
        return $this->hasOne(UpvoteNotification::class);
    }
    public function postVote()
    {
        return $this->hasOne(PostVote::class, 'vote_id');
    }
}
