# ArmaBattles Laravel Implementation Instructions

## Overview
This document contains everything needed to fix the API endpoints and add hit zone display functionality to the ArmaBattles Laravel website.

## Current Issues

### API Endpoints Returning 302 (Redirect)
These endpoints are not properly configured as API routes:
- `/api/player-stats`
- `/api/connections`
- `/api/base-events`
- `/api/building-events`
- `/api/consciousness-events`
- `/api/group-events`
- `/api/xp-events`
- `/api/chat-events`
- `/api/editor-actions`
- `/api/gm-sessions`

### Working Endpoints
- `/api/damage-events` - ✅ Works
- `/api/player-kills` - ✅ Works
- `/api/server-status` - ✅ Works

---

## Part 1: Fix API Routes

### File: `routes/api.php`

Add these routes (make sure they're inside the sanctum auth middleware group or properly authenticated):

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StatsController;

// Stats API routes - authenticated via API token
Route::middleware(['auth:sanctum'])->group(function () {
    // Player stats
    Route::post('/player-stats', [StatsController::class, 'storePlayerStats']);

    // Kill events
    Route::post('/player-kills', [StatsController::class, 'storeKill']);

    // Connection events
    Route::post('/connections', [StatsController::class, 'storeConnection']);

    // Base events (captures)
    Route::post('/base-events', [StatsController::class, 'storeBaseEvent']);

    // Building events
    Route::post('/building-events', [StatsController::class, 'storeBuildingEvent']);

    // Consciousness events (knockouts, wake ups)
    Route::post('/consciousness-events', [StatsController::class, 'storeConsciousnessEvent']);

    // Group events (join, leave, create)
    Route::post('/group-events', [StatsController::class, 'storeGroupEvent']);

    // XP events
    Route::post('/xp-events', [StatsController::class, 'storeXpEvent']);

    // Damage events (with hit zones)
    Route::post('/damage-events', [StatsController::class, 'storeDamageEvents']);

    // Chat events
    Route::post('/chat-events', [StatsController::class, 'storeChatEvent']);

    // Editor/GM actions
    Route::post('/editor-actions', [StatsController::class, 'storeEditorAction']);

    // GM sessions (enter/exit game master mode)
    Route::post('/gm-sessions', [StatsController::class, 'storeGmSession']);

    // Server status
    Route::post('/server-status', [StatsController::class, 'storeServerStatus']);

    // Distance traveled
    Route::post('/distance-events', [StatsController::class, 'storeDistance']);

    // Healing events
    Route::post('/healing-events', [StatsController::class, 'storeHealing']);

    // Supply deliveries
    Route::post('/supply-deliveries', [StatsController::class, 'storeSupplyDelivery']);

    // Grenade events
    Route::post('/grenade-events', [StatsController::class, 'storeGrenadeEvent']);

    // Shooting stats
    Route::post('/shooting-events', [StatsController::class, 'storeShootingEvent']);
});
```

---

## Part 2: Database Migrations

### Create migration for damage_events table (if not exists)

```bash
php artisan make:migration create_damage_events_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id');
            $table->string('damage_type')->default('KINETIC');
            $table->decimal('damage_amount', 10, 2)->default(0);
            $table->string('hit_zone_name')->nullable();
            $table->string('killer_name')->nullable();
            $table->string('killer_uuid')->nullable();
            $table->integer('killer_id')->default(0);
            $table->string('killer_faction')->nullable();
            $table->string('victim_name')->nullable();
            $table->string('victim_uuid')->nullable();
            $table->integer('victim_id')->default(0);
            $table->string('victim_faction')->nullable();
            $table->string('weapon_name')->nullable();
            $table->decimal('distance', 10, 2)->default(0);
            $table->boolean('is_friendly_fire')->default(false);
            $table->timestamp('event_timestamp')->nullable();
            $table->timestamps();

            $table->index('killer_uuid');
            $table->index('victim_uuid');
            $table->index('hit_zone_name');
            $table->index('is_friendly_fire');
            $table->index('server_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_events');
    }
};
```

### Add missing columns to damage_events (if table exists but missing columns)

```bash
php artisan make:migration add_hit_zone_columns_to_damage_events
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('damage_events', function (Blueprint $table) {
            if (!Schema::hasColumn('damage_events', 'hit_zone_name')) {
                $table->string('hit_zone_name')->nullable()->after('damage_amount');
            }
            if (!Schema::hasColumn('damage_events', 'is_friendly_fire')) {
                $table->boolean('is_friendly_fire')->default(false)->after('distance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('damage_events', function (Blueprint $table) {
            $table->dropColumn(['hit_zone_name', 'is_friendly_fire']);
        });
    }
};
```

---

## Part 3: Model

### File: `app/Models/DamageEvent.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'damage_type',
        'damage_amount',
        'hit_zone_name',
        'killer_name',
        'killer_uuid',
        'killer_id',
        'killer_faction',
        'victim_name',
        'victim_uuid',
        'victim_id',
        'victim_faction',
        'weapon_name',
        'distance',
        'is_friendly_fire',
        'event_timestamp',
    ];

    protected $casts = [
        'damage_amount' => 'decimal:2',
        'distance' => 'decimal:2',
        'is_friendly_fire' => 'boolean',
        'event_timestamp' => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
```

---

## Part 4: Controller Methods

### File: `app/Http/Controllers/Api/StatsController.php`

Add or update these methods:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DamageEvent;
use App\Models\PlayerKill;
use App\Models\Connection;
use App\Models\BaseEvent;
use App\Models\BuildingEvent;
use App\Models\ConsciousnessEvent;
use App\Models\GroupEvent;
use App\Models\XpEvent;
use App\Models\ChatEvent;
use App\Models\EditorAction;
use App\Models\GmSession;
use App\Models\ServerStatus;
use App\Models\PlayerStat;
use App\Models\DistanceEvent;
use App\Models\HealingEvent;
use App\Models\SupplyDelivery;
use App\Models\GrenadeEvent;
use App\Models\ShootingEvent;

class StatsController extends Controller
{
    /**
     * Store damage events (with hit zones and friendly fire)
     */
    public function storeDamageEvents(Request $request)
    {
        $events = $request->input('events', []);
        $inserted = 0;

        foreach ($events as $event) {
            DamageEvent::create([
                'server_id' => $event['server_id'] ?? 1,
                'damage_type' => $event['damage_type'] ?? 'KINETIC',
                'damage_amount' => $event['damage_amount'] ?? 0,
                'hit_zone_name' => $event['hit_zone_name'] ?? null,
                'killer_name' => $event['killer_name'] ?? null,
                'killer_uuid' => $event['killer_uuid'] ?? null,
                'killer_id' => $event['killer_id'] ?? 0,
                'killer_faction' => $event['killer_faction'] ?? null,
                'victim_name' => $event['victim_name'] ?? null,
                'victim_uuid' => $event['victim_uuid'] ?? null,
                'victim_id' => $event['victim_id'] ?? 0,
                'victim_faction' => $event['victim_faction'] ?? null,
                'weapon_name' => $event['weapon_name'] ?? null,
                'distance' => $event['distance'] ?? 0,
                'is_friendly_fire' => $event['is_friendly_fire'] ?? false,
                'event_timestamp' => $event['timestamp'] ?? now(),
            ]);
            $inserted++;
        }

        return response()->json(['success' => true, 'inserted' => $inserted]);
    }

    /**
     * Store player stats
     */
    public function storePlayerStats(Request $request)
    {
        $data = $request->validate([
            'server_id' => 'required|integer',
            'player_uuid' => 'required|string',
            'player_name' => 'required|string',
            'kills' => 'integer',
            'deaths' => 'integer',
            'score' => 'integer',
            'playtime' => 'integer',
        ]);

        $stat = PlayerStat::updateOrCreate(
            ['player_uuid' => $data['player_uuid'], 'server_id' => $data['server_id']],
            $data
        );

        return response()->json(['success' => true, 'id' => $stat->id]);
    }

    /**
     * Store connection event
     */
    public function storeConnection(Request $request)
    {
        $data = $request->validate([
            'server_id' => 'required|integer',
            'player_uuid' => 'required|string',
            'player_name' => 'required|string',
            'event_type' => 'required|string', // CONNECT or DISCONNECT
            'timestamp' => 'nullable|string',
        ]);

        $connection = Connection::create([
            'server_id' => $data['server_id'],
            'player_uuid' => $data['player_uuid'],
            'player_name' => $data['player_name'],
            'event_type' => $data['event_type'],
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $connection->id]);
    }

    /**
     * Store base event (capture)
     */
    public function storeBaseEvent(Request $request)
    {
        $data = $request->all();

        $event = BaseEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'base_name' => $data['base_name'] ?? null,
            'event_type' => $data['event_type'] ?? 'CAPTURE',
            'faction' => $data['faction'] ?? null,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store building event
     */
    public function storeBuildingEvent(Request $request)
    {
        $data = $request->all();

        $event = BuildingEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'building_type' => $data['building_type'] ?? null,
            'event_type' => $data['event_type'] ?? 'BUILD',
            'position_x' => $data['position_x'] ?? null,
            'position_y' => $data['position_y'] ?? null,
            'position_z' => $data['position_z'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store consciousness event (knockout/wakeup)
     */
    public function storeConsciousnessEvent(Request $request)
    {
        $data = $request->all();

        $event = ConsciousnessEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'event_type' => $data['event_type'] ?? 'KNOCKOUT',
            'cause' => $data['cause'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store group event
     */
    public function storeGroupEvent(Request $request)
    {
        $data = $request->all();

        $event = GroupEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'group_name' => $data['group_name'] ?? null,
            'event_type' => $data['event_type'] ?? 'JOIN',
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store XP event
     */
    public function storeXpEvent(Request $request)
    {
        $data = $request->all();

        $event = XpEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'xp_amount' => $data['xp_amount'] ?? 0,
            'xp_type' => $data['xp_type'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store chat event
     */
    public function storeChatEvent(Request $request)
    {
        $data = $request->all();

        $event = ChatEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'message' => $data['message'] ?? '',
            'channel' => $data['channel'] ?? 'Global',
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store editor action (GM action)
     */
    public function storeEditorAction(Request $request)
    {
        $data = $request->all();

        $event = EditorAction::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'action' => $data['action'] ?? null,
            'hovered_entity_component_name' => $data['hovered_entity_component_name'] ?? null,
            'selected_entity_components_names' => $data['selected_entity_components_names'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store GM session (enter/exit game master)
     */
    public function storeGmSession(Request $request)
    {
        $data = $request->all();

        $event = GmSession::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'event_type' => $data['event_type'] ?? 'GM_ENTER',
            'duration' => $data['duration'] ?? 0,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store server status
     */
    public function storeServerStatus(Request $request)
    {
        $data = $request->all();

        $status = ServerStatus::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_count' => $data['player_count'] ?? 0,
            'status' => $data['status'] ?? 'online',
            'map_name' => $data['map_name'] ?? null,
            'recorded_at' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $status->id]);
    }

    /**
     * Store distance traveled
     */
    public function storeDistance(Request $request)
    {
        $data = $request->all();

        $event = DistanceEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'distance' => $data['distance'] ?? 0,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store healing event
     */
    public function storeHealing(Request $request)
    {
        $data = $request->all();

        $event = HealingEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'healer_uuid' => $data['healer_uuid'] ?? null,
            'healer_name' => $data['healer_name'] ?? null,
            'target_uuid' => $data['target_uuid'] ?? null,
            'target_name' => $data['target_name'] ?? null,
            'heal_type' => $data['heal_type'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store supply delivery
     */
    public function storeSupplyDelivery(Request $request)
    {
        $data = $request->all();

        $event = SupplyDelivery::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'supply_type' => $data['supply_type'] ?? 'delivery',
            'amount' => $data['amount'] ?? 0,
            'delivered_at' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store grenade event
     */
    public function storeGrenadeEvent(Request $request)
    {
        $data = $request->all();

        $event = GrenadeEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'grenade_type' => $data['grenade_type'] ?? null,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store shooting event
     */
    public function storeShootingEvent(Request $request)
    {
        $data = $request->all();

        $event = ShootingEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'weapon_name' => $data['weapon_name'] ?? null,
            'shots_fired' => $data['shots_fired'] ?? 0,
            'hits' => $data['hits'] ?? 0,
            'event_timestamp' => $data['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }
}
```

---

## Part 5: Hit Zone Statistics Service

### File: `app/Services/HitZoneService.php`

```php
<?php

namespace App\Services;

use App\Models\DamageEvent;
use Illuminate\Support\Facades\DB;

class HitZoneService
{
    /**
     * Get hit zone statistics for a player (as attacker)
     */
    public function getPlayerHitZoneStats(string $playerUuid): array
    {
        // Hit zones dealt (where player was the attacker)
        $dealt = DamageEvent::where('killer_uuid', $playerUuid)
            ->whereNotNull('hit_zone_name')
            ->select('hit_zone_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(damage_amount) as total_damage'))
            ->groupBy('hit_zone_name')
            ->orderByDesc('count')
            ->get();

        // Hit zones received (where player was the victim)
        $received = DamageEvent::where('victim_uuid', $playerUuid)
            ->whereNotNull('hit_zone_name')
            ->select('hit_zone_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(damage_amount) as total_damage'))
            ->groupBy('hit_zone_name')
            ->orderByDesc('count')
            ->get();

        // Friendly fire stats
        $friendlyFireDealt = DamageEvent::where('killer_uuid', $playerUuid)
            ->where('is_friendly_fire', true)
            ->count();

        $friendlyFireReceived = DamageEvent::where('victim_uuid', $playerUuid)
            ->where('is_friendly_fire', true)
            ->count();

        // Total damage stats
        $totalDamageDealt = DamageEvent::where('killer_uuid', $playerUuid)
            ->sum('damage_amount');

        $totalDamageReceived = DamageEvent::where('victim_uuid', $playerUuid)
            ->sum('damage_amount');

        // Headshot percentage
        $totalHits = $dealt->sum('count');
        $headshots = $dealt->where('hit_zone_name', 'HEAD')->first();
        $headshotCount = $headshots ? $headshots->count : 0;
        $headshotPercentage = $totalHits > 0 ? round(($headshotCount / $totalHits) * 100, 1) : 0;

        return [
            'dealt' => $dealt,
            'received' => $received,
            'total_hits_dealt' => $totalHits,
            'total_damage_dealt' => round($totalDamageDealt, 2),
            'total_damage_received' => round($totalDamageReceived, 2),
            'headshot_count' => $headshotCount,
            'headshot_percentage' => $headshotPercentage,
            'friendly_fire_dealt' => $friendlyFireDealt,
            'friendly_fire_received' => $friendlyFireReceived,
        ];
    }

    /**
     * Get weapon accuracy statistics for a player
     */
    public function getPlayerWeaponStats(string $playerUuid): array
    {
        return DamageEvent::where('killer_uuid', $playerUuid)
            ->whereNotNull('weapon_name')
            ->select(
                'weapon_name',
                DB::raw('COUNT(*) as hits'),
                DB::raw('SUM(damage_amount) as total_damage'),
                DB::raw('AVG(distance) as avg_distance'),
                DB::raw('MAX(distance) as max_distance'),
                DB::raw("SUM(CASE WHEN hit_zone_name = 'HEAD' THEN 1 ELSE 0 END) as headshots")
            )
            ->groupBy('weapon_name')
            ->orderByDesc('hits')
            ->get()
            ->toArray();
    }

    /**
     * Format hit zone name for display
     */
    public static function formatHitZoneName(string $hitZone): string
    {
        $names = [
            'HEAD' => 'Head',
            'UPPERTORSO' => 'Upper Torso',
            'LOWERTORSO' => 'Lower Torso',
            'LEFTARM' => 'Left Arm',
            'RIGHTARM' => 'Right Arm',
            'LEFTLEG' => 'Left Leg',
            'RIGHTLEG' => 'Right Leg',
            'SCR_CharacterResilienceHitZone' => 'Body',
        ];

        return $names[$hitZone] ?? ucfirst(strtolower(str_replace('_', ' ', $hitZone)));
    }
}
```

---

## Part 6: Player Controller Update

### Add to existing PlayerController or create new method:

```php
<?php

use App\Services\HitZoneService;

class PlayerController extends Controller
{
    /**
     * Show player profile with hit zone stats
     */
    public function show(string $uuid)
    {
        $player = Player::where('uuid', $uuid)->firstOrFail();

        $hitZoneService = new HitZoneService();
        $hitZoneStats = $hitZoneService->getPlayerHitZoneStats($uuid);
        $weaponStats = $hitZoneService->getPlayerWeaponStats($uuid);

        return view('players.show', compact('player', 'hitZoneStats', 'weaponStats'));
    }

    /**
     * API endpoint for hit zone stats
     */
    public function hitZoneStats(string $uuid)
    {
        $hitZoneService = new HitZoneService();

        return response()->json([
            'hit_zones' => $hitZoneService->getPlayerHitZoneStats($uuid),
            'weapons' => $hitZoneService->getPlayerWeaponStats($uuid),
        ]);
    }
}
```

---

## Part 7: Blade View Component

### File: `resources/views/components/hit-zone-stats.blade.php`

```blade
@props(['stats'])

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Hit Zone Accuracy</h5>
        <span class="badge bg-primary">{{ $stats['headshot_percentage'] }}% Headshots</span>
    </div>
    <div class="card-body">
        @if($stats['total_hits_dealt'] > 0)
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Damage Dealt by Zone</h6>
                    @foreach($stats['dealt'] as $zone)
                        @php
                            $percentage = ($zone->count / $stats['total_hits_dealt']) * 100;
                            $zoneName = \App\Services\HitZoneService::formatHitZoneName($zone->hit_zone_name);
                            $barColor = $zone->hit_zone_name === 'HEAD' ? 'bg-danger' : 'bg-primary';
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>{{ $zoneName }}</span>
                                <span>{{ $zone->count }} hits ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $barColor }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="col-md-6">
                    <h6>Summary</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Total Hits</td>
                            <td class="text-end">{{ number_format($stats['total_hits_dealt']) }}</td>
                        </tr>
                        <tr>
                            <td>Total Damage Dealt</td>
                            <td class="text-end">{{ number_format($stats['total_damage_dealt']) }}</td>
                        </tr>
                        <tr>
                            <td>Headshots</td>
                            <td class="text-end">{{ number_format($stats['headshot_count']) }}</td>
                        </tr>
                        <tr>
                            <td>Headshot Rate</td>
                            <td class="text-end">{{ $stats['headshot_percentage'] }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        @else
            <p class="text-muted">No damage data available yet.</p>
        @endif

        @if($stats['friendly_fire_dealt'] > 0)
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle"></i>
                Friendly Fire Incidents: {{ $stats['friendly_fire_dealt'] }} dealt, {{ $stats['friendly_fire_received'] }} received
            </div>
        @endif
    </div>
</div>
```

### File: `resources/views/components/weapon-stats.blade.php`

```blade
@props(['weapons'])

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Weapon Statistics</h5>
    </div>
    <div class="card-body">
        @if(count($weapons) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Weapon</th>
                            <th class="text-end">Hits</th>
                            <th class="text-end">Damage</th>
                            <th class="text-end">Headshots</th>
                            <th class="text-end">Avg Distance</th>
                            <th class="text-end">Max Distance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weapons as $weapon)
                            <tr>
                                <td>{{ $weapon['weapon_name'] }}</td>
                                <td class="text-end">{{ number_format($weapon['hits']) }}</td>
                                <td class="text-end">{{ number_format($weapon['total_damage']) }}</td>
                                <td class="text-end">{{ number_format($weapon['headshots']) }}</td>
                                <td class="text-end">{{ number_format($weapon['avg_distance'], 1) }}m</td>
                                <td class="text-end">{{ number_format($weapon['max_distance'], 1) }}m</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No weapon data available yet.</p>
        @endif
    </div>
</div>
```

---

## Part 8: Usage in Player Profile View

### Add to player profile blade (e.g., `resources/views/players/show.blade.php`):

```blade
{{-- Include hit zone stats component --}}
<x-hit-zone-stats :stats="$hitZoneStats" />

{{-- Include weapon stats component --}}
<x-weapon-stats :weapons="$weaponStats" />
```

---

## Part 9: RCON Panel Fix

The RCON panel might not be working due to JavaScript issues. Check the RconController and ensure it makes proper HTTP requests to the game server.

### File: `app/Http/Controllers/Admin/RconController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RconController extends Controller
{
    protected $rconApiUrl;
    protected $rconApiKey;

    public function __construct()
    {
        $this->rconApiUrl = env('RCON_API_URL', 'http://78.109.17.18:3001');
        $this->rconApiKey = env('RCON_API_KEY', '1|4qnSdk3rVAKhrryrh8e2RyfZ0hPEL8DEne4DcCAld2020282');
    }

    public function index()
    {
        return view('admin.rcon.index');
    }

    public function status()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->rconApiKey,
            ])->timeout(10)->get($this->rconApiUrl . '/rcon/status');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function players()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->rconApiKey,
            ])->timeout(10)->get($this->rconApiUrl . '/rcon/players');

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function command(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->rconApiKey,
            ])->timeout(30)->post($this->rconApiUrl . '/rcon/command', [
                'command' => $request->input('command'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function kick(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->rconApiKey,
            ])->timeout(10)->post($this->rconApiUrl . '/rcon/kick', [
                'playerId' => $request->input('playerId'),
                'reason' => $request->input('reason', 'Kicked by admin'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function ban(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->rconApiKey,
            ])->timeout(10)->post($this->rconApiUrl . '/rcon/ban', [
                'playerId' => $request->input('playerId'),
                'minutes' => $request->input('minutes', 0),
                'reason' => $request->input('reason', 'Banned by admin'),
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function say(Request $request)
    {
        // Note: Say command is not supported in Arma Reforger RCON
        return response()->json(['error' => 'Say command not supported in Arma Reforger'], 400);
    }
}
```

### RCON Routes in `routes/web.php`:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/rcon', [App\Http\Controllers\Admin\RconController::class, 'index'])->name('admin.rcon');
    Route::get('/rcon/status', [App\Http\Controllers\Admin\RconController::class, 'status']);
    Route::get('/rcon/players', [App\Http\Controllers\Admin\RconController::class, 'players']);
    Route::post('/rcon/command', [App\Http\Controllers\Admin\RconController::class, 'command']);
    Route::post('/rcon/kick', [App\Http\Controllers\Admin\RconController::class, 'kick']);
    Route::post('/rcon/ban', [App\Http\Controllers\Admin\RconController::class, 'ban']);
});
```

---

## Part 10: Environment Variables

### Add to `.env`:

```env
RCON_API_URL=http://78.109.17.18:3001
RCON_API_KEY=1|4qnSdk3rVAKhrryrh8e2RyfZ0hPEL8DEne4DcCAld2020282
```

---

## Summary of Tasks

1. **Run migrations** to add/update tables
2. **Update routes/api.php** with all API endpoints
3. **Create/Update StatsController** with all store methods
4. **Create DamageEvent model** if it doesn't exist
5. **Create HitZoneService** for stats calculations
6. **Add Blade components** for hit zone display
7. **Update PlayerController** to include hit zone stats
8. **Fix RconController** for proper API communication
9. **Add environment variables** for RCON

Run these commands after making changes:
```bash
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Data Flow

```
Game Server (Arma Reforger)
    ↓
RJS Logger Mod (logs events)
    ↓
Stats Collector (Node.js on 78.109.17.18)
    ↓ POST /api/damage-events
Laravel API (armabattles.com)
    ↓
Database (PostgreSQL)
    ↓
Player Profile View (with hit zone stats)
```
