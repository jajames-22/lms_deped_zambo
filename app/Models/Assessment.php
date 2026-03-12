<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;

class Assessment extends Model
{
    protected $fillable = ['title', 'year_level', 'description', 'access_key'];

    public function categories()
    {
        // Explicitly stating the foreign key 'assessment_id'
        return $this->hasMany(AssessmentCategory::class, 'assessment_id');
    }

    // 2. Questions Relationship
    public function questions()
    {
        return $this->hasManyThrough(
            AssessmentQuestion::class, // The final model we want to access
            AssessmentCategory::class, // The intermediate model we go through
            'assessment_id',           // Foreign key on the assessment_categories table
            'category_id',             // Foreign key on the assessment_questions table (This fixes it!)
            'id',                      // Local key on the assessments table
            'id'                       // Local key on the assessment_categories table
        );
    }
}