<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $table = 'report';
    const CREATED_AT = 'reportdate';
    const UPDATED_AT = null;
    protected $fillable = ['reason', 'reportdate', 'isopen', 'reporttype', 'authenticateduser_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
