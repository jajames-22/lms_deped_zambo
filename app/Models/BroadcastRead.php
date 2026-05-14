<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastRead extends Model
{
    use HasFactory;

    // Allow mass assignment for these columns
    protected $fillable = [
        'user_id',
        'broadcast_id',
    ];

    /**
     * Relationship: A read record belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A read record belongs to one broadcast
     */
    public function broadcast()
    {
        return $this->belongsTo(Broadcast::class);
    }
}