<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowNotification extends Notification
{
    use HasFactory;
    protected $table = 'follownotification';
    public $timestamps = false;

    protected $fillable = ['notification_id', 'authenticatedUser_id'];
    protected $primaryKey = ['notification_id', 'authenticatedUser_id'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function follower()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
