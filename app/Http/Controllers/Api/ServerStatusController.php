<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ServerStatusRequest;
use App\Models\GameServerStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ServerStatusController extends Controller
{
    public function store(ServerStatusRequest $request): JsonResponse
    {
        Log::info('Server status received', [
            'server_id' => $request->server_id,
            'server_name' => $request->server_name,
            'players' => $request->players,
        ]);

        $status = GameServerStatus::create([
            'server_id' => $request->server_id,
            'server_name' => $request->server_name,
            'map' => $request->map,
            'players' => $request->players,
            'max_players' => $request->max_players,
            'ping' => $request->ping,
            'timestamp' => Carbon::createFromTimestampMs($request->timestamp),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Server status recorded',
            'status_id' => $status->id,
        ]);
    }
}
