<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'lrn',
        'status'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // Link this to whatever User/Student model uses the LRN
    public function student()
    {
        return $this->belongsTo(User::class, 'lrn', 'lrn'); // Assuming 'lrn' column exists on the User model
    }
}