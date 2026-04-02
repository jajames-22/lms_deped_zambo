<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
