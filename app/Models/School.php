<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    public $timestamps = true;

    protected $table = 'schools';

    protected $fillable = [
        'school_id',   // Added this: User-provided ID (e.g., 305412)
        'name',
        'district_id',
        'logo',
        'address',
        'level'
    ];

    /**
     * A School belongs to one District
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}