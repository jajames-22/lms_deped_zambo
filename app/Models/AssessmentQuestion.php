<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'category_id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'option_f', 'correct_answer'
    ];

    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class);
    }
}