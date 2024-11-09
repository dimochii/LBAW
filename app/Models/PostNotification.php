<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNotification extends Notification
{
    use HasFactory;
    protected $table = 'postnotification';
    public $timestamps = false;

    protected $fillable = ['notification_id', 'post_id'];
    protected $primaryKey =['notification_id', 'post_id'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
