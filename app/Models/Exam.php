<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id', // Changed from lesson_id
        'type',
        'question_text',
        'media_url',
        'is_case_sensitive'
    ];

    // Belongs to the Material, not the Lesson
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function options()
    {
        // Links to the 'exam_options' table using 'exam_id'
        return $this->hasMany(ExamOption::class, 'exam_id');
    }
}