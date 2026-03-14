<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    public function materials()
    {
        return $this->belongsTo(Materials::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
