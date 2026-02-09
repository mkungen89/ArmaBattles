<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlayerConnectRequest;
use App\Models\Player;
use App\Models\PlayerSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PlayerSessionController extends Controller
{
    public function connect(PlayerConnectRequest $request): JsonResponse
    {
        Log::info('Player connected', [
            'player_name' => $request->player_name,
            'server_id' => $request->server_id,
        ]);

        $timestamp = Carbon::createFromTimestampMs($request->timestamp);

        $session = PlayerSession::create([
            'server_id' => $request->server_id,
            'player_name' => $request->player_name,
            'player_uuid' => $request->player_uuid,
            'event_type' => 'connect',
            'timestamp' => $timestamp,
        ]);

        // Create or update player record
        $playerData = [
            'player_name' => $request->player_name,
            'last_seen' => $timestamp,
            'server_id' => $request->server_id,
        ];

        if ($request->player_uuid) {
            $player = Player::updateOrCreate(
                ['uuid' => $request->player_uuid],
                $playerData
            );
        } else {
            $player = Player::updateOrCreate(
                ['player_name' => $request->player_name],
                $playerData
            );
        }

        if ($player->wasRecentlyCreated) {
            $player->update(['first_seen' => $timestamp]);
        }

        // Increment session count
        $player->increment('sessions');

        return response()->json([
            'success' => true,
            'message' => 'Data recorded',
            'id' => $session->id,
        ]);
    }

    public function disconnect(PlayerConnectRequest $request): JsonResponse
    {
        Log::info('Player disconnected', [
            'player_name' => $request->player_name,
            'server_id' => $request->server_id,
        ]);

        $timestamp = Carbon::createFromTimestampMs($request->timestamp);

        $session = PlayerSession::create([
            'server_id' => $request->server_id,
            'player_name' => $request->player_name,
            'player_uuid' => $request->player_uuid,
            'event_type' => 'disconnect',
            'timestamp' => $timestamp,
        ]);

        // Update player's last seen
        $query = $request->player_uuid
            ? Player::where('uuid', $request->player_uuid)
            : Player::where('player_name', $request->player_name);

        $query->update(['last_seen' => $timestamp]);

        return response()->json([
            'success' => true,
            'message' => 'Data recorded',
            'id' => $session->id,
        ]);
    }
}
