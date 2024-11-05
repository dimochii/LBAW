<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticatedUser extends Model
{
    use Notifiable, use HasFactory;
    protected $table = 'AuthenticatedUser';
    const CREATED_AT = 'creationDate';
    const UPDATED_AT = null;
    protected $fillable = [
        'name', 'username', 'email', 'password', 'reputation', 
        'isSuspended', 'creationDate', 'birthDate', 'description', 'isAdmin', 'imageID'
    ];

    public function image()
    {
        return $this->hasOne(Image::class);
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'CommunityFollower');
    }

}
