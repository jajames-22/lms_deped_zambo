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
        'exam_weight',         
        'passing_percentage',  
        'access_code',
        'thumbnail',
        'views',
        'downloads',           
        'is_public',
        'is_featured',
        'draft_json',
        'admin_remarks',     
        'evaluation_json'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean', 
    ];

    protected $appends = ['hashid']; 

    // Creates a custom $material->hashid attribute
    public function getHashidAttribute()
    {
        return \Vinkla\Hashids\Facades\Hashids::encode($this->id);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'material_id');
    }

    // Replaced accesses() with enrollments()
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'material_id');
    }

    public function tags()
    {
        // This tells Laravel: 
        // 1. Connect to the Tag model
        // 2. Use the 'material_tag' pivot table
        // 3. Match 'material_id' to 'tag_id'
        return $this->belongsToMany(Tag::class, 'material_tag', 'material_id', 'tag_id');
    }
    
    public function exams()
    {
        return $this->hasMany(Exam::class, 'material_id');
    }
}