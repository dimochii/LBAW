<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    const CREATED_AT = 'report_date';
    const UPDATED_AT = null;
    protected $fillable = ['reason', 'report_date', 'is_open', 'report_type', 'authenticated_user_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
