<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['title', 'year_level', 'description', 'access_key'];

    public function categories()
    {
        return $this->hasMany(AssessmentCategory::class);
    }
}