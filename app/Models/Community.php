<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;
    /*by default, Laravel assumes  public $timestamps = true, where $timestamps are created_at and updated_at*/ 
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = null; // we only want the created_at
    protected $fillable = ['name', 'description', 'creation_date', 'privacy', 'image_id'];

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id', 'id');  
    }

    public function posts() 
    {
        return $this->hasMany(Post::class);
    }

    public function moderators()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'community_moderators');
    }

    public function followers()
    {
        return $this->belongsToMany(AuthenticatedUser::class, 'community_followers');
    }

    public function followRequests(): HasMany
    {
        return $this->hasMany(CommunityFollowRequest::class, 'community_id');
    }
}
