<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'title',
        'time_limit'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function contents()
    {
        return $this->hasMany(LessonContent::class);
    }
}