<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSession extends Model
{
    use HasFactory;

    // Allow these columns to be saved via mass assignment
    protected $fillable = [
        'user_id',
        'assessment_id',
        'category_id',
        'time_remaining',
        'is_completed',
    ];
}