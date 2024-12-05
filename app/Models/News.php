<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Post
{
    use HasFactory;
    protected $primaryKey = 'post_id';
    public $timestamps = false;
    protected $fillable = ['post_id', 'news_url', 'image_url'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
