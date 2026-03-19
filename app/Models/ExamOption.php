<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'option_text',
        'is_correct'
    ];

    // Renamed from quiz() to exam()
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}