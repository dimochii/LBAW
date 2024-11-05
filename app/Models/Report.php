<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $table = 'Report';
    const CREATED_AT = 'reportDate';
    const UPDATED_AT = null;
    protected $fillable = ['reason', 'reportDate', 'isOpen', 'reportType', 'authenticatedUser_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
