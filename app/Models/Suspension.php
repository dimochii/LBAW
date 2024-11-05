<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    use HasFactory;
    protected $table = 'Suspension';
    public $timestamps = false;
    protected $fillable = ['reason', 'start', 'duration', 'authenticatedUser_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
