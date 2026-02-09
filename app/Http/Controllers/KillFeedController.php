<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KillFeedController extends Controller
{
    public function index()
    {
        $kills = DB::table('player_kills')
            ->orderByDesc('killed_at')
            ->limit(50)
            ->get();

        $weaponImages = Weapon::whereNotNull('image_path')
            ->pluck('image_path', 'name')
            ->toArray();

        return view('kill-feed', compact('kills', 'weaponImages'));
    }

    public function api(Request $request)
    {
        $since = $request->get('since');

        $query = DB::table('player_kills')
            ->orderByDesc('killed_at')
            ->limit(50);

        if ($since) {
            $query->where('killed_at', '>', $since);
        }

        return response()->json($query->get());
    }
}
