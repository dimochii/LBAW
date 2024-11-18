<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TopicStatus;

class Topic extends Post
{
    use HasFactory;
    protected $primaryKey = 'post_id';
    const CREATED_AT = null;
    const UPDATED_AT = 'review_date';
    protected $casts = [
        'status' => TopicStatus::class,
    ];
    protected $fillable = ['post_id', 'review_date', 'status'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
