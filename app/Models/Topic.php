<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Post
{
    use HasFactory;
    protected $table = 'Topic';
    public $timestamps = false;
    protected $fillable = ['post_id', 'reviewDate', 'status'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
