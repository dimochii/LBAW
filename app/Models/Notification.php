<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notification'; 
    const CREATED_AT = 'notificationDate';
    const UPDATED_AT = null;

    protected $fillable = ['isRead','notificationDate','authenticatedUser_id'];


    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }

    public function followNotification()
    {
        return $this->hasOne(FollowNotification::class);
    }

    public function upvoteNotification()
    {
        return $this->hasOne(UpvoteNotification::class);
    }

    public function commentNotification()
    {
        return $this->hasOne(CommentNotification::class);
    }

    public function postNotification()
    {
        return $this->hasOne(PostNotification::class);
    }
}
