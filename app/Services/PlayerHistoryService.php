<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PlayerHistoryService
{
    public function search(string $query, ?int $serverId = null, int $limit = 50): array
    {
        $builder = DB::table('connections')
            ->select([
                'player_uuid',
                DB::raw('MAX(player_name) as player_name'),
                DB::raw('MAX(occurred_at) as last_seen'),
                DB::raw('COUNT(*) as connection_count'),
            ])
            ->whereNotNull('player_uuid')
            ->where('player_uuid', '!=', '');

        // Add alternative names — use string_agg for PostgreSQL, group_concat for SQLite/MySQL
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $builder->addSelect(DB::raw("string_agg(DISTINCT player_name, ', ') as alt_names"));
        } else {
            $builder->addSelect(DB::raw('GROUP_CONCAT(DISTINCT player_name) as alt_names'));
        }

        if ($serverId) {
            $builder->where('server_id', $serverId);
        }

        $builder->where(function ($q) use ($query) {
            $q->where('player_name', 'ILIKE', "%{$query}%")
                ->orWhere('player_uuid', 'ILIKE', "%{$query}%");
        });

        return $builder
            ->groupBy('player_uuid')
            ->orderByDesc('last_seen')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getPlayerDetail(string $uuid, ?int $serverId = null): array
    {
        $query = DB::table('connections')
            ->where('player_uuid', $uuid);

        if ($serverId) {
            $query->where('server_id', $serverId);
        }

        $connections = $query->orderByDesc('occurred_at')->get();

        $names = $connections->pluck('player_name')->unique()->values()->toArray();

        return [
            'uuid' => $uuid,
            'primary_name' => $names[0] ?? 'Unknown',
            'alt_names' => $names,
            'first_seen' => $connections->min('occurred_at'),
            'last_seen' => $connections->max('occurred_at'),
            'total_connections' => $connections->count(),
            'connections' => $connections->toArray(),
        ];
    }
}
