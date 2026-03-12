<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AssessmentAccess extends Model
{
    protected $fillable = [
        'assessment_id', 
        'lrn', 
        'status'
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    // 2. Keep the function name 'student', but point it to the User class!
    public function student()
    {
        return $this->belongsTo(User::class, 'lrn', 'user_id');
    }
}