<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'category_id',
        'text',
        'type',        // <-- ADD THIS
        'media_url',  // <-- ADD THIS
    ];

    public function options()
    {
        return $this->hasMany(AssessmentOption::class);
    }

    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class);
    }
}