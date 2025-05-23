<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentNotification extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['notification_id', 'comment_id'];


    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
