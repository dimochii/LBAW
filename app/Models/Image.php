<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model 
{
    use HasFactory;
    //protected $table = 'image';
    public $timestamps = false;
    protected $fillable = ['path'];

    public function community() {
        return $this->belongsTo(Community::class,'image_id','id');  
    }

    public function authenticatedUser()
    {
        return $this->hasOne(AuthenticatedUser::class, 'image_id', 'id');  
    }


}
