# API Rate Limiting

## Overview

The Reforger Community API implements tiered rate limiting based on token types. Each API token has a specific rate limit that determines how many requests can be made per minute.

## Token Types

| Type | Rate Limit | Ability | Use Case |
|------|------------|---------|----------|
| **Standard** | 60 req/min | `*` | Development and low-traffic servers |
| **High-Volume** | 180 req/min | `*`, `high-volume` | Production servers with moderate traffic |
| **Premium** | 300 req/min | `*`, `premium` | High-traffic production servers |

## Implementation

### Middleware

The `ApiRateLimiter` middleware checks token abilities to determine the appropriate rate limit:

```php
// app/Http/Middleware/ApiRateLimiter.php
protected function getRateLimit($token): int
{
    $abilities = $token->abilities ?? [];

    if (in_array('premium', $abilities)) {
        return 300;
    }

    if (in_array('high-volume', $abilities)) {
        return 180;
    }

    return 60; // standard
}
```

### Token Creation

Tokens are created with specific abilities in the admin panel:

```php
// Standard token
$token = $user->createToken('server-1', ['*']);

// High-volume token
$token = $user->createToken('server-2', ['*', 'high-volume']);

// Premium token
$token = $user->createToken('server-3', ['*', 'premium']);
```

## Rate Limit Headers

All API responses include rate limit information:

| Header | Description | Example |
|--------|-------------|---------|
| `X-RateLimit-Limit` | Maximum requests per minute | `300` |
| `X-RateLimit-Remaining` | Remaining requests in current window | `245` |
| `X-RateLimit-Reset` | Unix timestamp when limit resets | `1738742400` |
| `Retry-After` | Seconds to wait (only on 429 response) | `42` |

## Response Examples

### Successful Request (200 OK)

```http
HTTP/1.1 200 OK
X-RateLimit-Limit: 180
X-RateLimit-Remaining: 175
X-RateLimit-Reset: 1738742400
Content-Type: application/json

{
  "success": true,
  "data": {...}
}
```

### Rate Limit Exceeded (429 Too Many Requests)

```http
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1738742400
Retry-After: 42
Content-Type: application/json

{
  "error": "Too Many Requests",
  "message": "Rate limit exceeded. Please try again later.",
  "retry_after": 42
}
```

## Best Practices

### 1. Check Rate Limit Headers

Always check the `X-RateLimit-Remaining` header to track your usage:

```javascript
const response = await fetch('/api/player-stats', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const remaining = response.headers.get('X-RateLimit-Remaining');
if (remaining < 10) {
  console.warn('Approaching rate limit!');
}
```

### 2. Handle 429 Responses

Implement exponential backoff when hitting rate limits:

```javascript
async function apiRequest(url, token, retries = 3) {
  for (let i = 0; i < retries; i++) {
    const response = await fetch(url, {
      headers: { 'Authorization': `Bearer ${token}` }
    });

    if (response.status === 429) {
      const retryAfter = response.headers.get('Retry-After');
      await sleep((retryAfter || Math.pow(2, i)) * 1000);
      continue;
    }

    return response;
  }

  throw new Error('Rate limit exceeded after retries');
}
```

### 3. Batch Requests

Use batch endpoints when available to reduce API calls:

```javascript
// ❌ Bad: 100 separate requests
for (const kill of kills) {
  await fetch('/api/player-kills', {
    method: 'POST',
    body: JSON.stringify(kill)
  });
}

// ✅ Good: 1 batch request
await fetch('/api/damage-events', {
  method: 'POST',
  body: JSON.stringify({ data: kills })
});
```

### 4. Cache Responses

Cache GET responses locally to reduce redundant API calls:

```javascript
const cache = new Map();

async function getCachedPlayer(uuid, token) {
  if (cache.has(uuid)) {
    return cache.get(uuid);
  }

  const response = await fetch(`/api/players/${uuid}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });

  const data = await response.json();
  cache.set(uuid, data);

  return data;
}
```

## Upgrading Token Type

To upgrade your token to a higher tier:

1. Navigate to **Admin Panel** → **Game Stats** → **API Tokens**
2. Revoke your existing token
3. Generate a new token with the desired type (High-Volume or Premium)
4. Update your application with the new token

## Monitoring

Monitor your API usage in the admin panel:
- View rate limit for each token
- Check last used timestamp
- Track token creation date

## Technical Details

### Cache Key Format

Rate limits are tracked per token using cache keys:

```
api_rate_limit:{token_id}
```

### Time Window

Rate limits are calculated per **1 minute** sliding window.

### Rate Limiter

Uses Laravel's built-in `RateLimiter` with cache-based storage:

```php
$this->limiter->hit($key, 60); // 60 seconds decay
```

## Troubleshooting

### Problem: Hitting rate limits unexpectedly

**Solution:** Check if you're making redundant requests. Implement caching and batching.

### Problem: Rate limit not resetting

**Solution:** Rate limits use a sliding window. Wait 60 seconds after your first request in the window.

### Problem: Different rate limit than expected

**Solution:** Verify your token type in the admin panel. The token abilities determine the rate limit.

## Related Files

- **Middleware:** `app/Http/Middleware/ApiRateLimiter.php`
- **Controller:** `app/Http/Controllers/Admin/GameStatsAdminController.php`
- **Routes:** `routes/api.php`
- **View:** `resources/views/admin/game-stats/api-tokens.blade.php`

## See Also

- [CLAUDE.md](../CLAUDE.md) - Project overview and API architecture
- [PERFORMANCE.md](PERFORMANCE.md) - Database optimization and caching
- [API Documentation](../routes/api.php) - Full list of available endpoints
