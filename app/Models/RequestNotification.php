<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestNotification extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['notification_id', 'request_id'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function request()
    {
        return $this->belongsTo(CommunityFollowRequest::class);
    }
}
