<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materials()
    {
        return $this->belongsTo(Materials::class);
    }

    protected $fillable = [
        'user_id',
        'materials_id'
    ];
}

