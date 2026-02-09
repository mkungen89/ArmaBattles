<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'label', 'description', 'options', 'sort_order'];

    protected const CACHE_KEY = 'site_settings_all';

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllCached();

        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = $settings[$key];

        return static::castValue($setting['value'], $setting['type']);
    }

    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        }

        static::clearCache();
    }

    public static function getAllCached(): array
    {
        try {
            return Cache::remember(static::CACHE_KEY, 3600, function () {
                return static::all()->keyBy('key')->map(function ($setting) {
                    return [
                        'value' => $setting->value,
                        'type' => $setting->type,
                    ];
                })->toArray();
            });
        } catch (QueryException) {
            return [];
        }
    }

    public static function getAllGrouped(): array
    {
        return static::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->toArray();
    }

    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
