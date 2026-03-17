<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    protected $fillable = ['assessment_id', 'title', 'time_limit'];

    public function questions()
    {
        // Add 'category_id' as the second parameter
        return $this->hasMany(AssessmentQuestion::class, 'category_id');
    }

    public function options()
    {
        // Add 'question_id' as the second parameter
        return $this->hasMany(AssessmentOption::class, 'question_id');
    }
}