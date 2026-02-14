<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerRole extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the role's icon or default
     */
    public function getIconAttribute($value): string
    {
        return $value ?? 'user';
    }

    /**
     * Get all available categories
     */
    public static function getCategories(): array
    {
        return [
            'infantry' => 'Infantry',
            'support' => 'Support',
            'specialist' => 'Specialist',
            'leadership' => 'Leadership',
        ];
    }

    /**
     * Get default roles (for seeding)
     */
    public static function getDefaultRoles(): array
    {
        return [
            // Infantry
            ['name' => 'rifleman', 'display_name' => 'Rifleman', 'icon' => 'rifle', 'category' => 'infantry', 'description' => 'Standard infantry role with assault rifle'],
            ['name' => 'grenadier', 'display_name' => 'Grenadier', 'icon' => 'bomb', 'category' => 'infantry', 'description' => 'Equipped with grenade launcher for suppression'],
            ['name' => 'light_at', 'display_name' => 'Light AT', 'icon' => 'target', 'category' => 'infantry', 'description' => 'Anti-tank specialist with light AT weapons'],

            // Support
            ['name' => 'medic', 'display_name' => 'Medic', 'icon' => 'heart-pulse', 'category' => 'support', 'description' => 'Combat medic providing medical support'],
            ['name' => 'engineer', 'display_name' => 'Engineer', 'icon' => 'wrench', 'category' => 'support', 'description' => 'Builds fortifications and repairs vehicles'],
            ['name' => 'ammo_bearer', 'display_name' => 'Ammo Bearer', 'icon' => 'package', 'category' => 'support', 'description' => 'Supplies ammunition to the squad'],

            // Specialist
            ['name' => 'sniper', 'display_name' => 'Sniper', 'icon' => 'crosshair', 'category' => 'specialist', 'description' => 'Long-range precision shooter'],
            ['name' => 'spotter', 'display_name' => 'Spotter', 'icon' => 'eye', 'category' => 'specialist', 'description' => 'Reconnaissance and target designation'],
            ['name' => 'machine_gunner', 'display_name' => 'Machine Gunner', 'icon' => 'zap', 'category' => 'specialist', 'description' => 'Provides sustained suppressive fire'],

            // Leadership
            ['name' => 'squad_leader', 'display_name' => 'Squad Leader', 'icon' => 'users', 'category' => 'leadership', 'description' => 'Commands and coordinates squad operations'],
            ['name' => 'platoon_leader', 'display_name' => 'Platoon Leader', 'icon' => 'shield', 'category' => 'leadership', 'description' => 'Commands multiple squads'],
        ];
    }
}
