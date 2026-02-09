# Roadkill Tracking - Laravel Implementation Prompt

## Context

Gameserverns stats collector skickar nu ett nytt fält `is_roadkill` med kill-events. En kill räknas som roadkill när spelaren sitter i ett fordon och `damageType` är `COLLISION`. Node.js-sidan är redan uppdaterad och skickar detta fält till `/api/player-kills`.

Du behöver:

1. Migration för `player_kills` - lägg till `is_roadkill` kolumn
2. Migration för `players` - lägg till `total_roadkills` kolumn
3. Uppdatera `PlayerKill` model
4. Uppdatera `Player` model
5. Uppdatera `StatsController` - validation, räknare och ny leaderboard endpoint
6. Lägg till route för roadkill leaderboard

---

## Inkommande data

`POST /api/player-kills` skickar nu ett extra fält:

```json
{
  "server_id": 1,
  "killer_name": "PlayerName",
  "killer_uuid": "uuid",
  "killer_in_vehicle": true,
  "killer_vehicle": "UAZ-469",
  "killer_vehicle_prefab": "ukr_uaz_469_open_o",
  "is_roadkill": true,
  "weapon_name": "UAZ-469",
  "damage_type": "COLLISION",
  "kill_distance": 0,
  "...": "övriga befintliga fält"
}
```

| Fält | Typ | Beskrivning |
|------|-----|-------------|
| `is_roadkill` | boolean | `true` om spelaren körde ihjäl någon med fordon (collision damage) |

---

## Migration 1: player_kills

Lägg till `is_roadkill` efter `is_team_kill`:

```php
Schema::table('player_kills', function (Blueprint $table) {
    if (!Schema::hasColumn('player_kills', 'is_roadkill')) {
        $table->boolean('is_roadkill')->default(false)->after('is_team_kill');
    }
});
```

## Migration 2: players

Lägg till `total_roadkills` efter `total_team_kills`:

```php
Schema::table('players', function (Blueprint $table) {
    if (!Schema::hasColumn('players', 'total_roadkills')) {
        $table->integer('total_roadkills')->default(0)->after('total_team_kills');
    }
});
```

---

## Model-ändringar

### PlayerKill

Lägg till i `$fillable`:
```php
'is_roadkill',
```

Lägg till i `$casts`:
```php
'is_roadkill' => 'boolean',
```

Lägg till scope:
```php
public function scopeRoadkills($query)
{
    return $query->where('is_roadkill', true);
}
```

### Player

Lägg till i `$fillable`:
```php
'total_roadkills',
```

---

## Controller-ändringar

### StatsController

**storeKill validation** - lägg till:
```php
'is_roadkill' => 'nullable|boolean',
```

**updatePlayerOnKill** - lägg till i killer update-blocket:
```php
if ($data['is_roadkill'] ?? false) {
    $update['total_roadkills'] = \DB::raw('total_roadkills + 1');
}
```

**Ny endpoint** - roadkill leaderboard:
```php
public function getRoadkillLeaderboard(Request $request): JsonResponse
{
    $players = Player::where('total_roadkills', '>', 0)
        ->orderByDesc('total_roadkills')
        ->limit($request->get('limit', 100))
        ->get(['id', 'uuid', 'name', 'platform', 'total_roadkills', 'total_kills']);

    return response()->json($players);
}
```

---

## Route

Lägg till i API routes (samma mönster som övriga leaderboards):

```php
Route::get('/leaderboard/roadkills', [StatsController::class, 'getRoadkillLeaderboard']);
```

---

## Efter implementation

Kör:
```bash
php artisan migrate
```

Verifiera med:
```bash
php artisan tinker
>>> Schema::hasColumn('player_kills', 'is_roadkill')
>>> Schema::hasColumn('players', 'total_roadkills')
```
