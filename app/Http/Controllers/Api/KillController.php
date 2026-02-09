<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KillBatchRequest;
use App\Models\Kill;
use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KillController extends Controller
{
    public function batch(KillBatchRequest $request): JsonResponse
    {
        $kills = $request->validated()['data'];
        $inserted = 0;

        Log::info('Kill batch received', ['count' => count($kills)]);

        DB::transaction(function () use ($kills, &$inserted) {
            foreach ($kills as $killData) {
                $killer = Player::firstOrCreate(
                    ['player_name' => $killData['killer']],
                    [
                        'first_seen' => Carbon::createFromTimestampMs($killData['timestamp']),
                        'last_seen' => Carbon::createFromTimestampMs($killData['timestamp']),
                    ]
                );

                $victim = Player::firstOrCreate(
                    ['player_name' => $killData['victim']],
                    [
                        'first_seen' => Carbon::createFromTimestampMs($killData['timestamp']),
                        'last_seen' => Carbon::createFromTimestampMs($killData['timestamp']),
                    ]
                );

                Kill::create([
                    'server_id' => $killData['server_id'],
                    'killer_id' => $killer->id,
                    'victim_id' => $victim->id,
                    'killer_name' => $killData['killer'],
                    'victim_name' => $killData['victim'],
                    'weapon' => $killData['weapon'],
                    'timestamp' => Carbon::createFromTimestampMs($killData['timestamp']),
                ]);

                $inserted++;
            }
        });

        Log::info('Kill batch processed', ['inserted' => $inserted]);

        return response()->json([
            'success' => true,
            'message' => "Processed {$inserted} kills",
            'inserted' => $inserted,
        ]);
    }
}
