<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'instructor_id',
        'status',
        'thumbnail',
        'views',
        'is_public',
        'draft_json'
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'materials_id');
    }

    // Replaced accesses() with enrollments()
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'materials_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}