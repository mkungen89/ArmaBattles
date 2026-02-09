<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mod extends Model
{
    use HasFactory;

    protected $fillable = [
        'workshop_id',
        'name',
        'author',
        'author_url',
        'version',
        'description',
        'workshop_url',
        'thumbnail_url',
        'file_size',
        'subscriptions',
        'workshop_updated_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'subscriptions' => 'integer',
        'workshop_updated_at' => 'datetime',
    ];

    /**
     * Get all servers using this mod
     */
    public function servers(): BelongsToMany
    {
        return $this->belongsToMany(Server::class, 'server_mod')
            ->withPivot(['load_order', 'is_required'])
            ->withTimestamps();
    }

    /**
     * Get workshop URL
     */
    public function getWorkshopLinkAttribute(): string
    {
        if ($this->workshop_url) {
            return $this->workshop_url;
        }

        return "https://reforger.armaplatform.com/workshop/{$this->workshop_id}";
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get time since last update
     */
    public function getTimeSinceUpdateAttribute(): string
    {
        if (!$this->workshop_updated_at) return 'Unknown';

        return $this->workshop_updated_at->diffForHumans();
    }

    /**
     * Create or update mod from BattleMetrics data
     */
    public static function syncFromBattleMetrics(array $modData): self
    {
        return self::updateOrCreate(
            ['workshop_id' => $modData['id'] ?? $modData['workshop_id'] ?? uniqid()],
            [
                'name' => $modData['name'] ?? 'Unknown Mod',
                'author' => $modData['author'] ?? null,
                'version' => $modData['version'] ?? null,
                'workshop_url' => $modData['url'] ?? $modData['workshop_url'] ?? null,
                'workshop_updated_at' => isset($modData['updated_at'])
                    ? \Carbon\Carbon::parse($modData['updated_at'])
                    : null,
            ]
        );
    }
}
