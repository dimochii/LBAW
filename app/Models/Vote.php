<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;
    protected $table = 'Vote';
    public $timestamps = false;
    protected $fillable = ['upvote', 'authenticatedUser_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AuthenticatedUser::class);
    }
}
