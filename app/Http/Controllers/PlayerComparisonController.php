<?php

namespace App\Http\Controllers;

use App\Models\PlayerStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerComparisonController extends Controller
{
    public function index(Request $request)
    {
        $playerKeys = ['p1', 'p2', 'p3', 'p4'];
        $players = [];
        $weapons = [];
        $uuids = [];

        foreach ($playerKeys as $key) {
            $uuid = $request->get($key);
            if ($uuid) {
                $uuids[$key] = $uuid;
                $stat = PlayerStat::where('player_uuid', $uuid)->first();
                if ($stat) {
                    $players[$key] = $stat;
                    $weapons[$key] = DB::table('player_kills')
                        ->select('weapon_name', DB::raw('COUNT(*) as total'))
                        ->where('killer_uuid', $uuid)
                        ->groupBy('weapon_name')
                        ->orderByDesc('total')
                        ->limit(10)
                        ->get();
                }
            }
        }

        return view('players.compare', compact('players', 'weapons', 'uuids'));
    }

    public function headToHead(Request $request): JsonResponse
    {
        $p1 = $request->get('p1');
        $p2 = $request->get('p2');

        if (!$p1 || !$p2) {
            return response()->json(['error' => 'Two player UUIDs required'], 400);
        }

        // Kills: p1 killed p2
        $p1KilledP2 = DB::table('player_kills')
            ->where('killer_uuid', $p1)
            ->where('victim_uuid', $p2)
            ->count();

        // Kills: p2 killed p1
        $p2KilledP1 = DB::table('player_kills')
            ->where('killer_uuid', $p2)
            ->where('victim_uuid', $p1)
            ->count();

        // Most used weapon by p1 against p2
        $p1TopWeapon = DB::table('player_kills')
            ->select('weapon_name', DB::raw('COUNT(*) as total'))
            ->where('killer_uuid', $p1)
            ->where('victim_uuid', $p2)
            ->groupBy('weapon_name')
            ->orderByDesc('total')
            ->first();

        // Most used weapon by p2 against p1
        $p2TopWeapon = DB::table('player_kills')
            ->select('weapon_name', DB::raw('COUNT(*) as total'))
            ->where('killer_uuid', $p2)
            ->where('victim_uuid', $p1)
            ->groupBy('weapon_name')
            ->orderByDesc('total')
            ->first();

        // Last 10 encounters between them
        $recentEncounters = DB::table('player_kills')
            ->select('killer_uuid', 'killer_name', 'victim_uuid', 'victim_name', 'weapon_name', 'distance', 'is_headshot', 'created_at')
            ->where(function ($q) use ($p1, $p2) {
                $q->where(function ($q2) use ($p1, $p2) {
                    $q2->where('killer_uuid', $p1)->where('victim_uuid', $p2);
                })->orWhere(function ($q2) use ($p1, $p2) {
                    $q2->where('killer_uuid', $p2)->where('victim_uuid', $p1);
                });
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'p1_killed_p2' => $p1KilledP2,
            'p2_killed_p1' => $p2KilledP1,
            'p1_top_weapon' => $p1TopWeapon?->weapon_name,
            'p2_top_weapon' => $p2TopWeapon?->weapon_name,
            'recent_encounters' => $recentEncounters,
        ]);
    }

    public function searchPlayer(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = PlayerStat::where('player_name', 'ILIKE', "%{$q}%")
            ->orderByDesc('kills')
            ->limit(10)
            ->get(['player_uuid', 'player_name', 'kills', 'deaths']);

        return response()->json($results->map(fn($p) => [
            'uuid' => $p->player_uuid,
            'name' => $p->player_name,
            'kills' => $p->kills,
            'deaths' => $p->deaths,
        ]));
    }
}
