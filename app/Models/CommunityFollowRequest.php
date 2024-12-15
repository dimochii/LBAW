<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityFollowRequest extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'authenticated_user_id',
        'community_id',
        'request_status',
        'request_date',
    ];

    public function community() {
        return $this->belongsTo(Community::class, 'community_id');
    }

    public function user() {
        return $this->belongsTo(AuthenticatedUser::class, 'authenticated_user_id');
    }

    
}
