<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ReportType;


class Report extends Model
{
    use HasFactory;
    const CREATED_AT = 'report_date';
    const UPDATED_AT = null;
    protected $casts = [
        'report_type' => ReportType::class,
    ];
    protected $fillable = ['reason', 'report_date', 'is_open', 'report_type', 'authenticated_user_id'];

    public function user()
    {
        return $this->belongsTo(AuthenticatedUser::class, 'authenticated_user_id'); 
    }
}
