<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Post
{
    use HasFactory;
    protected $table = 'topic';
    const CREATED_AT = null;
    const UPDATED_AT = 'reviewdate';
    protected $fillable = ['post_id', 'reviewdate', 'status'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
