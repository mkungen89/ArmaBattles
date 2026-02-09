<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $server->name }} - Server Status</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: transparent;
        }
        .widget {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid {{ $theme === 'dark' ? '#374151' : '#e5e7eb' }};
            background: {{ $theme === 'dark' ? '#1f2937' : '#ffffff' }};
            color: {{ $theme === 'dark' ? '#f3f4f6' : '#1f2937' }};
        }
        .widget-header {
            padding: {{ $compact ? '10px 14px' : '14px 18px' }};
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid {{ $theme === 'dark' ? '#374151' : '#e5e7eb' }};
        }
        .server-name {
            font-weight: 600;
            font-size: {{ $compact ? '13px' : '15px' }};
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 70%;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-online {
            background: {{ $accent }}22;
            color: {{ $accent }};
        }
        .status-offline {
            background: #ef444422;
            color: #ef4444;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        .status-dot.online { background: {{ $accent }}; }
        .status-dot.offline { background: #ef4444; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .widget-body {
            padding: {{ $compact ? '10px 14px' : '14px 18px' }};
        }
        .player-bar-wrap {
            margin-bottom: {{ $compact ? '8px' : '12px' }};
        }
        .player-count {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 6px;
        }
        .player-label {
            font-size: 12px;
            color: {{ $theme === 'dark' ? '#9ca3af' : '#6b7280' }};
        }
        .player-numbers {
            font-size: {{ $compact ? '16px' : '20px' }};
            font-weight: 700;
        }
        .player-numbers .sep {
            color: {{ $theme === 'dark' ? '#4b5563' : '#d1d5db' }};
            font-weight: 400;
        }
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            background: {{ $theme === 'dark' ? '#374151' : '#e5e7eb' }};
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            background: {{ $accent }};
            transition: width 0.5s ease;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: {{ $compact ? '6px' : '8px' }};
        }
        .meta-item {
            font-size: 12px;
        }
        .meta-label {
            color: {{ $theme === 'dark' ? '#6b7280' : '#9ca3af' }};
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-value {
            font-weight: 500;
            margin-top: 1px;
        }
        .widget-footer {
            padding: 8px 18px;
            border-top: 1px solid {{ $theme === 'dark' ? '#374151' : '#e5e7eb' }};
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            color: {{ $theme === 'dark' ? '#6b7280' : '#9ca3af' }};
        }
        .widget-footer a {
            color: {{ $accent }};
            text-decoration: none;
            font-weight: 500;
        }
        .widget-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="widget">
        <div class="widget-header">
            <span class="server-name">{{ $server->name }}</span>
            @php $isOnline = ($server->status ?? 'offline') === 'online'; @endphp
            <span class="status-badge {{ $isOnline ? 'status-online' : 'status-offline' }}">
                <span class="status-dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                {{ $isOnline ? 'Online' : 'Offline' }}
            </span>
        </div>

        <div class="widget-body">
            @php
                $players = $server->players ?? 0;
                $maxPlayers = $server->max_players ?? 1;
                $pct = $maxPlayers > 0 ? round(($players / $maxPlayers) * 100) : 0;
            @endphp
            <div class="player-bar-wrap">
                <div class="player-count">
                    <span class="player-label">Players</span>
                    <span class="player-numbers">{{ $players }} <span class="sep">/</span> {{ $maxPlayers }}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $pct }}%"></div>
                </div>
            </div>

            @if(!$compact)
            <div class="meta-grid">
                @if($server->scenario_display_name)
                <div class="meta-item">
                    <div class="meta-label">Scenario</div>
                    <div class="meta-value">{{ $server->scenario_display_name }}</div>
                </div>
                @endif
                @if($server->map)
                <div class="meta-item">
                    <div class="meta-label">Map</div>
                    <div class="meta-value">{{ $server->map }}</div>
                </div>
                @endif
                @if($server->game_version)
                <div class="meta-item">
                    <div class="meta-label">Version</div>
                    <div class="meta-value">v{{ $server->game_version }}</div>
                </div>
                @endif
                @if($server->ip)
                <div class="meta-item">
                    <div class="meta-label">Address</div>
                    <div class="meta-value">{{ $server->ip }}:{{ $server->port }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>

        <div class="widget-footer">
            <span>Updated {{ $server->updated_at?->diffForHumans() ?? 'N/A' }}</span>
            <a href="{{ route('servers.show', $server) }}" target="_blank">View on {{ site_setting('site_name', config('app.name')) }}</a>
        </div>
    </div>

    <script>
        // Auto-refresh every 60 seconds
        setTimeout(() => location.reload(), 60000);
    </script>
</body>
</html>
