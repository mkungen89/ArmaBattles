<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlayerStatsRequest;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PlayerStatsController extends Controller
{
    public function store(PlayerStatsRequest $request): JsonResponse
    {
        Log::info('Player stats received', [
            'player_uuid' => $request->player_uuid,
            'server_id' => $request->server_id,
        ]);

        $timestamp = Carbon::createFromTimestampMs($request->timestamp);

        $player = Player::updateOrCreate(
            ['uuid' => $request->player_uuid],
            [
                'player_name' => $request->player_name ?? 'Unknown',
                'total_playtime' => $request->playtime,
                'kills' => $request->kills,
                'deaths' => $request->deaths,
                'xp' => $request->xp ?? 0,
                'distance_traveled' => $request->distance_traveled ?? 0,
                'score' => $request->score ?? 0,
                'sessions' => $request->sessions ?? 1,
                'server_id' => $request->server_id,
                'last_seen' => $timestamp,
            ]
        );

        if ($player->wasRecentlyCreated) {
            $player->update(['first_seen' => $timestamp]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data recorded',
            'id' => $player->id,
        ]);
    }
}
