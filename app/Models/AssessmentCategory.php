<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    protected $fillable = ['assessment_id', 'title', 'time_limit'];

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}