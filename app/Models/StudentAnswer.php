<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    // Allow these columns to be saved via mass assignment
    protected $fillable = [
        'user_id',
        'assessment_id',
        'question_id',
        'selected_options',
        'answer_text',
    ];
}
