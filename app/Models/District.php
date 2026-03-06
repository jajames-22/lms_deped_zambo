<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    // Disables created_at and updated_at
    public $timestamps = false;

    protected $table = 'districts';

    protected $fillable = [
        'name',
        'quadrant_id'
    ];

    /**
     * A District belongs to one Quadrant
     */
    public function quadrant(): BelongsTo
    {
        return $this->belongsTo(Quadrant::class);
    }

    /**
     * A District has many Schools
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}