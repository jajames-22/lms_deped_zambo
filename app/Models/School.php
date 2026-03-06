<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    // Keeps timestamps enabled since the default migration included $table->timestamps()
    public $timestamps = true;

    protected $table = 'schools';

    protected $fillable = [
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