<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['reason', 'start', 'duration', 'authenticated_user_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
