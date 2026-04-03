<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'material_id',
        'status',
        'progress_data',
        'retakes', // <--- Add this here!
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // A student belongs to a user account
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}