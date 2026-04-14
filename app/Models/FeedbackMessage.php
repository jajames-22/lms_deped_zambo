<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackMessage extends Model
{
    protected $guarded = [];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}