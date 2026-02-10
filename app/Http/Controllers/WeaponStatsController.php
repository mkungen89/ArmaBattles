<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use Illuminate\Support\Facades\DB;

class WeaponStatsController extends Controller
{
    public function index()
    {
        $weapons = DB::table('player_kills')
            ->select(
                'weapon_name',
                DB::raw('COUNT(*) as total_kills'),
                DB::raw('SUM(CASE WHEN is_headshot THEN 1 ELSE 0 END) as headshots'),
                DB::raw('ROUND(AVG(kill_distance)::numeric, 1) as avg_distance'),
                DB::raw('MAX(kill_distance) as max_distance')
            )
            ->whereNotNull('weapon_name')
            ->where('weapon_name', '!=', '')
            ->groupBy('weapon_name')
            ->orderByDesc('total_kills')
            ->get()
            ->map(function ($w) {
                $w->headshot_pct = $w->total_kills > 0
                    ? round(($w->headshots / $w->total_kills) * 100, 1)
                    : 0;

                return $w;
            });

        $weaponImages = Weapon::whereNotNull('image_path')
            ->pluck('image_path', 'name')
            ->toArray();

        return view('weapons.index', compact('weapons', 'weaponImages'));
    }
}
