# API Versioning

## Overview

The Reforger Community API uses URI-based versioning to ensure backward compatibility and smooth migrations between API versions.

## Current Version

**Latest:** `/api/v1/` (introduced 2026-02-08)

## Version History

| Version | Status | Introduced | Deprecated | Sunset Date |
|---------|--------|------------|------------|-------------|
| v1 | ✅ Current | 2026-02-08 | - | - |
| Legacy (`/api/`) | ⚠️ Deprecated | 2025-01-01 | 2026-02-08 | 2026-06-01 |

## Migration Guide

### From Legacy API to v1

The v1 API is functionally identical to the legacy API but with improved organization and naming.

#### URL Changes

**Write Endpoints (POST):**
```
Legacy: /api/player-kills
v1:     /api/v1/player-kills
```

**Read Endpoints (GET):**
```
Legacy: /api/servers/{id}
v1:     /api/v1/servers/{id}

Legacy: /api/leaderboards/kills
v1:     /api/v1/leaderboards/kills

Legacy: /api/kills
v1:     /api/v1/events/kills

Legacy: /api/base-events
v1:     /api/v1/events/bases

Legacy: /api/chat
v1:     /api/v1/events/chat
```

#### Named Routes

All v1 routes have consistent naming:

```php
// Legacy
route('api.servers.status')

// v1
route('api.v1.servers.status')
```

### Quick Migration Checklist

1. **Update Base URL**
   ```javascript
   // Before
   const baseUrl = 'https://armabattles.se/api';

   // After
   const baseUrl = 'https://armabattles.se/api/v1';
   ```

2. **Update Event Endpoints**
   ```javascript
   // Before
   POST /api/kills
   POST /api/base-events
   POST /api/chat

   // After (same paths, just add /v1)
   POST /api/v1/kills
   POST /api/v1/base-events
   POST /api/v1/chat
   ```

3. **Update Event Read Endpoints**
   ```javascript
   // Before
   GET /api/kills
   GET /api/base-events
   GET /api/chat

   // After (organized under /events)
   GET /api/v1/events/kills
   GET /api/v1/events/bases    // NOTE: changed from "base-events"
   GET /api/v1/events/chat
   ```

4. **Test Your Integration**
   - Run your full test suite
   - Verify all endpoints return expected data
   - Check rate limit headers are present

## Deprecation Process

### Legacy API (`/api/*`)

**Status:** ⚠️ Deprecated as of 2026-02-08

All responses from legacy endpoints include deprecation headers:

```http
HTTP/1.1 200 OK
X-API-Deprecated: true
X-API-Deprecation-Date: 2026-02-08
X-API-Sunset-Date: 2026-06-01
X-API-Deprecation-Info: This endpoint is deprecated. Please migrate to /api/v1/
Deprecation: true
Sunset: Sat, 01 Jun 2026 00:00:00 GMT
Link: </api/v1/player-kills>; rel="alternate"; type="application/json"
```

**Timeline:**
- **2026-02-08:** Legacy API deprecated, deprecation headers added
- **2026-04-01:** Warning logs added for legacy API usage
- **2026-05-01:** Rate limits reduced for legacy API (30 req/min standard)
- **2026-06-01:** Legacy API removed (sunset date)

### Detecting Deprecation

**In JavaScript:**
```javascript
const response = await fetch('/api/player-kills', {
  headers: { 'Authorization': `Bearer ${token}` }
});

if (response.headers.get('X-API-Deprecated') === 'true') {
  const sunsetDate = response.headers.get('Sunset');
  const newUrl = response.headers.get('Link').match(/<(.+)>/)[1];

  console.warn(`API endpoint deprecated. Sunset: ${sunsetDate}. Migrate to: ${newUrl}`);
}
```

**In Python:**
```python
response = requests.post(
    'https://armabattles.se/api/player-kills',
    headers={'Authorization': f'Bearer {token}'},
    json=data
)

if response.headers.get('X-API-Deprecated') == 'true':
    print(f"⚠️ API deprecated. Migrate to: {response.headers.get('Link')}")
```

**In Bash:**
```bash
response=$(curl -i https://armabattles.se/api/player-kills \
  -H "Authorization: Bearer $TOKEN")

if echo "$response" | grep -q "X-API-Deprecated: true"; then
  echo "⚠️ This endpoint is deprecated. Please migrate to /api/v1/"
fi
```

## Version Selection

### Recommended: Use Latest Version

Always use the latest stable version (`/api/v1/`) for new integrations.

```javascript
// ✅ Good: Using latest version
const API_BASE = 'https://armabattles.se/api/v1';

// ❌ Bad: Using legacy API
const API_BASE = 'https://armabattles.se/api';
```

### Version Pinning

API versions are pinned in the URL path. There is no automatic upgrade mechanism.

```
/api/v1/player-kills  → Always uses v1
/api/v2/player-kills  → Will use v2 when available
```

## Breaking Changes Policy

### What Constitutes a Breaking Change?

New API version (v2, v3, etc.) will be introduced for:
- Removing endpoints
- Changing request/response formats
- Changing authentication requirements
- Changing rate limits significantly
- Renaming fields in responses

### What is NOT a Breaking Change?

These changes can occur within the same version:
- Adding new optional fields to requests
- Adding new fields to responses
- Adding new endpoints
- Improving performance
- Bug fixes
- Increasing rate limits

## API v1 Endpoints

### Write Endpoints (POST)

#### Server & Player Events
```
POST /api/v1/server-status
POST /api/v1/player-kills
POST /api/v1/player-stats
POST /api/v1/connections
```

#### Game Events
```
POST /api/v1/base-events
POST /api/v1/building-events
POST /api/v1/consciousness-events
POST /api/v1/group-events
POST /api/v1/xp-events
POST /api/v1/damage-events
POST /api/v1/chat-events
```

#### GM/Editor
```
POST /api/v1/editor-actions
POST /api/v1/gm-sessions
```

#### Player Actions (ReforgerJS)
```
POST /api/v1/player-shooting
POST /api/v1/player-distance
POST /api/v1/player-healing
POST /api/v1/player-grenades
POST /api/v1/player-supplies
POST /api/v1/supply-deliveries
POST /api/v1/player-reports
```

#### Anti-Cheat
```
POST /api/v1/anticheat-events
POST /api/v1/anticheat-stats
```

### Read Endpoints (GET)

#### Servers
```
GET /api/v1/servers
GET /api/v1/servers/{id}
GET /api/v1/servers/{id}/status
GET /api/v1/servers/{id}/players
```

#### Players
```
GET /api/v1/players
GET /api/v1/players/{id}
GET /api/v1/players/{id}/stats
GET /api/v1/players/{id}/kills
GET /api/v1/players/{id}/deaths
GET /api/v1/players/{id}/connections
GET /api/v1/players/{id}/xp
GET /api/v1/players/{id}/distance
GET /api/v1/players/{id}/shooting
```

#### Leaderboards
```
GET /api/v1/leaderboards/kills
GET /api/v1/leaderboards/deaths
GET /api/v1/leaderboards/kd
GET /api/v1/leaderboards/playtime
GET /api/v1/leaderboards/xp
GET /api/v1/leaderboards/distance
GET /api/v1/leaderboards/roadkills
```

#### Events/Logs
```
GET /api/v1/events/kills
GET /api/v1/events/connections
GET /api/v1/events/bases
GET /api/v1/events/chat
GET /api/v1/events/gm-sessions
```

#### Stats/Aggregates
```
GET /api/v1/stats/overview
GET /api/v1/stats/weapons
GET /api/v1/stats/factions
GET /api/v1/stats/bases
```

## Testing Versioned APIs

### cURL Examples

```bash
# v1 API
curl -X POST https://armabattles.se/api/v1/player-kills \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"killer":"Player1","victim":"Player2",...}'

# Legacy API (with deprecation warnings)
curl -i https://armabattles.se/api/player-kills \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"killer":"Player1","victim":"Player2",...}'
```

### Automated Testing

```javascript
describe('API Versioning', () => {
  it('should use v1 endpoints', async () => {
    const response = await fetch('/api/v1/player-kills', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(killData)
    });

    expect(response.status).toBe(200);
    expect(response.headers.get('X-API-Deprecated')).toBeNull();
  });

  it('should warn when using legacy API', async () => {
    const response = await fetch('/api/player-kills', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(killData)
    });

    expect(response.headers.get('X-API-Deprecated')).toBe('true');
    expect(response.headers.get('X-API-Sunset-Date')).toBe('2026-06-01');
  });
});
```

## Support & Questions

- **Documentation:** [/docs/CLAUDE.md](../CLAUDE.md)
- **Rate Limiting:** [/docs/API_RATE_LIMITING.md](API_RATE_LIMITING.md)
- **GitHub Issues:** https://github.com/armabattles/reforger-community/issues

## Changelog

### v1 (2026-02-08)

**Added:**
- Initial v1 release
- URI-based versioning
- Named routes for all endpoints
- Improved endpoint organization (events under `/events/`)
- Consistent naming conventions

**Changed:**
- Event read endpoints moved to `/events/` prefix
- `base-events` renamed to `events/bases`

**Deprecated:**
- Legacy `/api/*` endpoints (sunset: 2026-06-01)
