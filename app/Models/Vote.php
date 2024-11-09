<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;
    protected $table = 'vote';
    public $timestamps = false;
    protected $fillable = ['upvote', 'authenticateduser_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }

    public function voteNotification()
    {
        return $this->hasOne(UpvoteNotification::class);
    }
}
