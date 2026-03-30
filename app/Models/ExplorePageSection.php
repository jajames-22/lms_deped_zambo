<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExplorePageSection extends Model
{
    use HasFactory;

    // Allow these columns to be mass-assigned via the Controller
    protected $fillable = [
        'title',
        'subtitle',
        'tag_name',
        'order',
        'is_active',
    ];

    // Automatically cast these columns to specific data types
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}