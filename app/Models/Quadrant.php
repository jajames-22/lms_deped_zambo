<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quadrant extends Model
{
    // Disables created_at and updated_at since we removed them from the migration
    public $timestamps = false;

    protected $table = 'quadrants';

    protected $fillable = [
        'name'
    ];

    /**
     * A Quadrant has many Districts
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}