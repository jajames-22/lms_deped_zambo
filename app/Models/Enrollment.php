<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'materials_id',
        'email',
        'status'
    ];

    // A student belongs to a user account
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Changed from 'materials' to 'material' since it belongs to ONE material
    public function material()
    {
        return $this->belongsTo(Material::class, 'materials_id');
    }
}