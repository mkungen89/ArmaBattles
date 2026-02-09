<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Weapon extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'image_path',
        'weapon_type',
        'category',
    ];

    /**
     * Get the URL for the weapon image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }

    /**
     * Get display name or fall back to name
     */
    public function getDisplayAttribute(): string
    {
        return $this->display_name ?? $this->name;
    }

    /**
     * Find weapon by name, optionally create if not exists
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Get or create weapon by name
     */
    public static function findOrCreateByName(string $name): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            ['display_name' => $name]
        );
    }
}
