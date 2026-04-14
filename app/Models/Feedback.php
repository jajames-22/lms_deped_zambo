<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // Explicitly define the table name (Laravel usually guesses 'feedback' without the 's')
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'message',
        'media_url',
        'status',
        'admin_reply',
        'replied_by_admin_id',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    /**
     * Get the student or teacher who submitted the feedback.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(FeedbackMessage::class)->orderBy('created_at', 'asc');
    }
    /**
     * Get the admin who replied to this feedback.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'replied_by_admin_id');
    }

    /**
     * Helper to get the full URL of the screenshot
     */
    public function getMediaUrlAttribute($value)
    {
        if ($value) {
            return str_starts_with($value, 'http') ? $value : asset('storage/' . $value);
        }
        return null;
    }
}