<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Post
{
    use HasFactory;
    const CREATED_AT = null;
    const UPDATED_AT = 'review_date';
    protected $fillable = ['post_id', 'review_date', 'status'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
