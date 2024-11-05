<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model 
{
    use HasFactory;
    protected $table = 'Image';
    const CREATED_AT = 'creationDate';
    const UPDATED_AT = null;
    protected $fillable = ['path'];

    public function community() {
        return $this->belongsTo(Community::class);
    }

    public function authenticatedUser() {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
