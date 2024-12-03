<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpvoteNotification extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['notification_id', 'vote_id'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }
}
