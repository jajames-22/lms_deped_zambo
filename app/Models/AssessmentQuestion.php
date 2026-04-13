<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'category_id',
        'text',
        'type',        // <-- ADD THIS
        'media_url',  // <-- ADD THIS    // <-- ADD THIS
        'media_name'
    ];

    

    public function options()
    {
        // Tell Laravel exactly which column connects these two tables
        return $this->hasMany(AssessmentOption::class, 'question_id');
    }

    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class);
    }
}