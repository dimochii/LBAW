<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowNotification extends Notification
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['notification_id', 'authenticated_user_id'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function follower()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
