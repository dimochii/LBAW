<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;
    protected $table = 'community';
    /*by default, Laravel assumes  public $timestamps = true, where $timestamps are created_at and updated_at*/ 
    const CREATED_AT = 'creationdate';
    const UPDATED_AT = null; // we only want the created_at
    protected $fillable = ['name', 'description', 'creationdate', 'privacy', 'image_id'];

    public function image() 
    {
        return $this->hasOne(Image::class);
    }

    public function posts() 
    {
        return $this->hasMany(Post::class);
    }

    public function moderators()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'CommunityModerator');
    }

    public function followers()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'CommunityFollower');
    }
}
