<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Material is used for the reverse exclusiveMaterials relationship
// but we use the string class name to avoid circular boot issues.

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

    /**
     * Returns the exclusive template for a given material if one is set,
     * otherwise falls back to the globally active template.
     */
    public static function getForMaterial(\App\Models\Material $material): ?static
    {
        if ($material->exclusive_template_id) {
            $exclusive = static::find($material->exclusive_template_id);
            if ($exclusive) {
                return $exclusive;
            }
        }
        return static::getActive();
    }

    /**
     * Materials that use this template exclusively.
     */
    public function exclusiveMaterials()
    {
        return $this->hasMany(\App\Models\Material::class, 'exclusive_template_id');
    }
}
