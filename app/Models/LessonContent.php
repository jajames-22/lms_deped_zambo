<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'type',
        'question_text',
        'media_url',
        'media_name',
        'is_case_sensitive'
    ];

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'quiz_id'); 
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}