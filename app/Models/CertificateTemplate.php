<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'background_image',
        'elements',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'elements'   => 'array',
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Returns the single currently active template, or the default if none active.
     */
    public static function getActive(): ?static
    {
        return static::where('is_active', true)->first()
            ?? static::where('is_default', true)->first()
            ?? static::first();
    }
}
