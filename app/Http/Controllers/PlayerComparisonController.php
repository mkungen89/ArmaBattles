<?php

namespace App\Http\Controllers;

use App\Models\PlayerStat;
use App\Models\Weapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerComparisonController extends Controller
{
    public function index(Request $request)
    {
        $p1 = $request->get('p1');
        $p2 = $request->get('p2');
        $player1 = null;
        $player2 = null;
        $p1Weapons = collect();
        $p2Weapons = collect();

        if ($p1) {
            $player1 = PlayerStat::where('player_uuid', $p1)->first();
            if ($player1) {
                $p1Weapons = DB::table('player_kills')
                    ->select('weapon_name', DB::raw('COUNT(*) as total'))
                    ->where('killer_uuid', $p1)
                    ->groupBy('weapon_name')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get();
            }
        }

        if ($p2) {
            $player2 = PlayerStat::where('player_uuid', $p2)->first();
            if ($player2) {
                $p2Weapons = DB::table('player_kills')
                    ->select('weapon_name', DB::raw('COUNT(*) as total'))
                    ->where('killer_uuid', $p2)
                    ->groupBy('weapon_name')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get();
            }
        }

        return view('players.compare', compact('player1', 'player2', 'p1', 'p2', 'p1Weapons', 'p2Weapons'));
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
