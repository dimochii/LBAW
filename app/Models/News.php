<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Post
{
    use HasFactory;
    protected $table = 'News';
    public $timestamps = false;
    protected $fillable = ['postID', 'newsURL'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'postID');
    }
}
