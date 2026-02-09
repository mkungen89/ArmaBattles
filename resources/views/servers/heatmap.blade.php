@extends('layouts.app')

@section('title', 'Kill Heatmap - ' . $server->name)

@section('content')
<div class="min-h-screen" x-data="heatmapApp()" x-init="init()">
    {{-- Header --}}
    <div class="mb-4">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('servers.show', $serverId) }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-xl font-bold text-white">Kill Heatmap</h1>
            <span class="text-gray-400 text-sm">{{ $server->name }}</span>
        </div>

        {{-- Controls --}}
        <div class="flex flex-wrap items-center gap-3">
            {{-- Period filter --}}
            <div class="flex items-center gap-1 bg-gray-800 border border-gray-700 rounded-lg p-1">
                <template x-for="p in periods" :key="p.value">
                    <button
                        @click="setPeriod(p.value)"
                        :class="period === p.value ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:text-white'"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition"
                        x-text="p.label"
                    ></button>
                </template>
            </div>

            {{-- Type filter --}}
            <div class="flex items-center gap-1 bg-gray-800 border border-gray-700 rounded-lg p-1">
                <template x-for="t in types" :key="t.value">
                    <button
                        @click="setType(t.value)"
                        :class="type === t.value ? 'bg-blue-500/20 text-blue-400' : 'text-gray-400 hover:text-white'"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition"
                        x-text="t.label"
                    ></button>
                </template>
            </div>

            {{-- Radius slider --}}
            <div class="flex items-center gap-2 bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5">
                <span class="text-xs text-gray-400">Radius</span>
                <input type="range" min="5" max="50" x-model="radius" @input="updateHeat()" class="w-20 h-1 accent-green-500">
                <span class="text-xs text-gray-300 w-6 text-right" x-text="radius"></span>
            </div>

            {{-- Intensity slider --}}
            <div class="flex items-center gap-2 bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5">
                <span class="text-xs text-gray-400">Intensity</span>
                <input type="range" min="0.1" max="1" step="0.1" x-model="intensity" @input="updateHeat()" class="w-20 h-1 accent-green-500">
                <span class="text-xs text-gray-300 w-6 text-right" x-text="intensity"></span>
            </div>

            @if($canViewPlayers)
            {{-- Show Players toggle --}}
            <button
                @click="togglePlayers()"
                :class="showPlayers ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50' : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-white'"
                class="flex items-center gap-1.5 border rounded-lg px-3 py-1.5 text-xs font-medium transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-text="showPlayers ? 'Players (' + onlinePlayers.length + ')' : 'Show Players'"></span>
            </button>
            @endif

            {{-- Fullscreen --}}
            <button @click="toggleFullscreen()" class="flex items-center gap-1.5 bg-gray-800 border border-gray-700 hover:bg-gray-700 rounded-lg px-3 py-1.5 text-xs text-gray-300 hover:text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <template x-if="!fullscreen">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/>
                    </template>
                    <template x-if="fullscreen">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0v4m0-4h4m7 9l5 5m0 0v-4m0 4h-4M9 15l-5 5m0 0h4m-4 0v-4m11-7l5-5m0 0h-4m4 0v4"/>
                    </template>
                </svg>
                <span x-text="fullscreen ? 'Exit' : 'Fullscreen'"></span>
            </button>

            {{-- Stats --}}
            <div class="ml-auto flex items-center gap-4 text-xs">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                    <span class="text-gray-300"><span x-text="stats.kills">0</span> kills</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    <span class="text-gray-300"><span x-text="stats.deaths">0</span> deaths</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                    <span class="text-gray-300"><span x-text="stats.headshots">0</span> headshots</span>
                </div>
                <template x-if="loading">
                    <div class="flex items-center gap-1 text-gray-500">
                        <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Loading...
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Map Container --}}
    <div id="heatmap" class="w-full rounded-xl border border-gray-700 overflow-hidden" style="height: calc(100vh - 180px); background: #18181b;"></div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

<style>
    #heatmap {
        background: #18181b;
    }
    .leaflet-container {
        background: #18181b;
    }
    .leaflet-control-zoom a {
        background: #1f2937 !important;
        color: #d1d5db !important;
        border-color: #374151 !important;
    }
    .leaflet-control-zoom a:hover {
        background: #374151 !important;
        color: #fff !important;
    }
    .leaflet-control-attribution {
        background: rgba(31, 41, 55, 0.8) !important;
        color: #9ca3af !important;
    }
    .leaflet-control-attribution a {
        color: #60a5fa !important;
    }
    .kill-popup .leaflet-popup-content-wrapper {
        background: #1f2937;
        color: #e5e7eb;
        border: 1px solid #374151;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    .kill-popup .leaflet-popup-tip {
        background: #1f2937;
        border: 1px solid #374151;
    }
    .city-label {
        background: none !important;
        border: none !important;
    }
    .player-marker {
        background: none !important;
        border: none !important;
    }
    .player-dot {
        width: 10px;
        height: 10px;
        background: #10b981;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 6px rgba(16, 185, 129, 0.8), 0 0 12px rgba(16, 185, 129, 0.4);
        animation: player-pulse 2s ease-in-out infinite;
    }
    .player-name {
        position: absolute;
        left: 14px;
        top: -2px;
        white-space: nowrap;
        font-size: 11px;
        font-weight: 600;
        color: #10b981;
        text-shadow: 0 0 3px rgba(0,0,0,0.9), 1px 1px 2px rgba(0,0,0,0.8);
    }
    @keyframes player-pulse {
        0%, 100% { box-shadow: 0 0 6px rgba(16, 185, 129, 0.8), 0 0 12px rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 10px rgba(16, 185, 129, 1), 0 0 20px rgba(16, 185, 129, 0.6); }
    }
    [x-data]:fullscreen {
        background: #111827;
        padding: 1rem;
    }
    [x-data]:fullscreen #heatmap {
        height: calc(100vh - 80px) !important;
        border-radius: 0.5rem;
    }
</style>

<script>
function heatmapApp() {
    return {
        map: null,
        heatLayer: null,
        markerLayer: null,
        cityLayer: null,
        cityData: [],
        points: [],
        loading: false,
        period: 'all',
        type: 'both',
        radius: 20,
        intensity: 0.5,
        periods: [
            { value: '24h', label: '24h' },
            { value: '7d', label: '7 Days' },
            { value: '30d', label: '30 Days' },
            { value: 'all', label: 'All Time' },
        ],
        types: [
            { value: 'both', label: 'Both' },
            { value: 'kills', label: 'Kills' },
            { value: 'deaths', label: 'Deaths' },
        ],
        fullscreen: false,
        showPlayers: false,
        onlinePlayers: [],
        playerLayer: null,
        playerPollInterval: null,
        stats: { kills: 0, deaths: 0, headshots: 0 },

        // Everon terrain: 12800 × 12800 m.
        // CRS.Simple mapping: 12800 game units = 256 CRS units (1 tile at zoom 0).
        // Scale factor = 50 (game / CRS).
        // Game X (east)  → CRS lng:  lng = x / 50
        // Game Z (north) → CRS lat:  lat = (z - 12800) / 50
        //   Z=12800 (north) → lat=0 (top), Z=0 (south) → lat=-256 (bottom).
        gameSize: 12800,
        crsSize: 256,

        gameToLatLng(x, z) {
            return [(z - this.gameSize) / 50, x / 50];
        },

        latLngToGame(lat, lng) {
            return { x: Math.round(lng * 50), z: Math.round(lat * 50 + this.gameSize) };
        },

        init() {
            this.$nextTick(() => this.initMap());
        },

        initMap() {
            if (this.map) return;

            const S = this.crsSize;
            const bounds = [[-S, 0], [0, S]];

            this.map = L.map('heatmap', {
                crs: L.CRS.Simple,
                minZoom: 0,
                maxZoom: 7,
                zoomSnap: 0.5,
                zoomDelta: 0.5,
                attributionControl: true,
                maxBounds: [[-S - 20, -20], [20, S + 20]],
                maxBoundsViscosity: 0.8,
            });

            L.tileLayer('https://maps.izurvive.com/maps/Reforger-Everon-Top/0.1.0/tiles/{z}/{x}/{y}.webp', {
                maxZoom: 7,
                minZoom: 0,
                tileSize: 256,
                noWrap: true,
                bounds: bounds,
                attribution: 'Map tiles &copy; <a href="https://www.izurvive.com/" target="_blank" rel="noopener">iZurvive</a>',
            }).addTo(this.map);

            this.map.fitBounds(bounds);

            // Heat layer
            this.heatLayer = L.heatLayer([], {
                radius: parseInt(this.radius),
                blur: 15,
                maxZoom: 7,
                max: parseFloat(this.intensity),
                gradient: {
                    0.0: '#0000ff',
                    0.25: '#00ffff',
                    0.5: '#00ff00',
                    0.75: '#ffff00',
                    1.0: '#ff0000',
                },
            }).addTo(this.map);

            this.markerLayer = L.layerGroup().addTo(this.map);

            // Coordinate display (game coords)
            const coordControl = L.control({ position: 'bottomleft' });
            coordControl.onAdd = () => {
                const div = L.DomUtil.create('div', 'leaflet-control');
                div.style.cssText = 'background:rgba(31,41,55,0.9);color:#d1d5db;padding:4px 8px;border-radius:4px;font-size:11px;border:1px solid #374151;';
                div.id = 'coord-display';
                div.innerHTML = 'X: 0 | Z: 0';
                return div;
            };
            coordControl.addTo(this.map);

            this.map.on('mousemove', (e) => {
                const el = document.getElementById('coord-display');
                if (el) {
                    const g = this.latLngToGame(e.latlng.lat, e.latlng.lng);
                    el.innerHTML = `X: ${g.x} | Z: ${g.z}`;
                }
            });

            this.cityLayer = L.layerGroup().addTo(this.map);
            this.playerLayer = L.layerGroup().addTo(this.map);

            this.map.on('zoomend', () => { this.updateMarkers(); this.updateCityNames(); });
            this.map.on('moveend', () => this.updateMarkers());

            document.addEventListener('fullscreenchange', () => {
                this.fullscreen = !!document.fullscreenElement;
                this.$nextTick(() => this.map.invalidateSize());
            });

            this.loadCityNames();
            this.fetchData();
        },

        setPeriod(p) {
            this.period = p;
            this.fetchData();
        },

        setType(t) {
            this.type = t;
            this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    server_id: '{{ $server->id }}',
                    period: this.period,
                    type: this.type,
                });

                const response = await fetch(`/api/v1/heatmap?${params}`);
                const data = await response.json();

                this.points = data.points || [];
                this.updateStats();
                this.rebuildHeat();
                this.updateMarkers();
            } catch (error) {
                console.error('Failed to fetch heatmap data:', error);
            } finally {
                this.loading = false;
            }
        },

        updateStats() {
            this.stats = {
                kills: this.points.filter(p => p.type === 'kill').length,
                deaths: this.points.filter(p => p.type === 'death').length,
                headshots: this.points.filter(p => p.headshot).length,
            };
        },

        rebuildHeat() {
            if (!this.heatLayer) return;

            const heatData = this.points.map(p => {
                const ll = this.gameToLatLng(p.x, p.z);
                return [ll[0], ll[1], 1];
            });

            this.heatLayer.setLatLngs(heatData);
            this.heatLayer.setOptions({
                radius: parseInt(this.radius),
                max: parseFloat(this.intensity),
            });
        },

        togglePlayers() {
            this.showPlayers = !this.showPlayers;
            if (this.showPlayers) {
                this.fetchPlayers();
                this.playerPollInterval = setInterval(() => this.fetchPlayers(), 30000);
            } else {
                if (this.playerPollInterval) clearInterval(this.playerPollInterval);
                this.playerPollInterval = null;
                this.onlinePlayers = [];
                this.playerLayer.clearLayers();
            }
        },

        async fetchPlayers() {
            try {
                const res = await fetch(`{{ route('servers.heatmap.players', $serverId) }}`);
                if (!res.ok) return;
                const data = await res.json();
                this.onlinePlayers = data.players || [];
                this.renderPlayers();
            } catch (e) {
                console.error('Failed to fetch players:', e);
            }
        },

        renderPlayers() {
            if (!this.playerLayer) return;
            this.playerLayer.clearLayers();
            if (!this.showPlayers) return;

            this.onlinePlayers.forEach(p => {
                const ll = this.gameToLatLng(p.x, p.z);
                const icon = L.divIcon({
                    className: 'player-marker',
                    html: `<div class="player-dot"></div><span class="player-name">${p.name}</span>`,
                    iconSize: [10, 10],
                    iconAnchor: [5, 5],
                });
                const marker = L.marker(ll, { icon, zIndexOffset: 1000 })
                    .bindPopup(
                        `<div class="text-xs">
                            <div class="font-bold text-emerald-400">${p.name}</div>
                            <div class="text-gray-400">X: ${Math.round(p.x)} | Z: ${Math.round(p.z)}</div>
                            <div class="text-gray-500">${p.updated ? 'Last event: ' + new Date(p.updated).toLocaleString() : ''}</div>
                        </div>`,
                        { className: 'kill-popup', closeButton: false }
                    );
                this.playerLayer.addLayer(marker);
            });
        },

        toggleFullscreen() {
            const container = this.$root;
            if (!document.fullscreenElement) {
                container.requestFullscreen().then(() => {
                    this.fullscreen = true;
                    this.$nextTick(() => this.map.invalidateSize());
                }).catch(() => {});
            } else {
                document.exitFullscreen().then(() => {
                    this.fullscreen = false;
                    this.$nextTick(() => this.map.invalidateSize());
                }).catch(() => {});
            }
        },

        updateHeat() {
            if (!this.heatLayer) return;
            this.heatLayer.setOptions({
                radius: parseInt(this.radius),
                max: parseFloat(this.intensity),
            });
            this.heatLayer.redraw();
        },

        // Convert iZurvive WGS84 lat/lng to our CRS.Simple coordinates
        wgs84ToSimple(lat, lng) {
            const d = Math.PI / 180;
            const r = 6378137;
            const R = 20037508.342789244;
            const mx = lng * d * r;
            const my = Math.log(Math.tan(Math.PI / 4 + lat * d / 2)) * r;
            const total = 2 * R;
            return [(my - R) / total * 256, (mx + R) / total * 256];
        },

        async loadCityNames() {
            try {
                const res = await fetch('/data/everon-citynames.json');
                this.cityData = await res.json();
                this.updateCityNames();
            } catch (e) {
                console.error('Failed to load city names:', e);
            }
        },

        updateCityNames() {
            if (!this.cityLayer || !this.map) return;
            this.cityLayer.clearLayers();

            const zoom = this.map.getZoom();
            const shadow = '0 0 3px rgba(255,255,255,0.9), 0 0 6px rgba(255,255,255,0.6), 1px 1px 2px rgba(255,255,255,0.5)';
            const typeStyles = {
                'City':        { size: 15, color: '#000000', weight: 800 },
                'Town':        { size: 13, color: '#000000', weight: 700 },
                'Settlement':  { size: 11, color: '#111827', weight: 600 },
                'Village':     { size: 11, color: '#111827', weight: 600 },
                'Sea Major':   { size: 12, color: '#1e3a5f', weight: 600, italic: true },
                'Sea Minor':   { size: 10, color: '#1e3a5f', weight: 500, italic: true },
                'Water Major': { size: 11, color: '#1e3a5f', weight: 600, italic: true },
                'Water Minor': { size: 10, color: '#1e3a5f', weight: 500, italic: true },
                'Hill':        { size: 10, color: '#374151', weight: 600 },
                'Ridge':       { size: 10, color: '#374151', weight: 600 },
                'Valley':      { size: 10, color: '#374151', weight: 600 },
                'Local':       { size: 10, color: '#1f2937', weight: 600 },
                'Generic':     { size: 10, color: '#1f2937', weight: 600 },
                'Ruin':        { size: 10, color: '#1f2937', weight: 600 },
                'Island':      { size: 10, color: '#1e3a5f', weight: 600, italic: true },
            };

            this.cityData.forEach(c => {
                if (zoom < c.minZoom) return;

                const ll = this.wgs84ToSimple(c.lat, c.lng);
                const style = typeStyles[c.type] || { size: 10, color: '#1f2937', weight: 600 };
                const fs = style.italic ? 'italic' : 'normal';
                const icon = L.divIcon({
                    className: 'city-label',
                    html: `<span style="font-size:${style.size}px;color:${style.color};font-weight:${style.weight};font-style:${fs};white-space:nowrap;text-shadow:${shadow};">${c.nameEN}</span>`,
                    iconSize: null,
                    iconAnchor: [0, 0],
                });
                this.cityLayer.addLayer(L.marker(ll, { icon, interactive: false }));
            });
        },

        // Show individual dots when zoomed in enough
        updateMarkers() {
            if (!this.markerLayer || !this.map) return;
            this.markerLayer.clearLayers();

            if (this.map.getZoom() < 4) return;

            const bounds = this.map.getBounds();
            const visible = this.points.filter(p => {
                const ll = this.gameToLatLng(p.x, p.z);
                return ll[0] >= bounds.getSouth() && ll[0] <= bounds.getNorth() &&
                       ll[1] >= bounds.getWest()  && ll[1] <= bounds.getEast();
            }).slice(0, 200);

            visible.forEach(p => {
                const ll = this.gameToLatLng(p.x, p.z);
                const color = p.type === 'kill' ? '#ef4444' : '#3b82f6';
                const icon = L.divIcon({
                    className: '',
                    html: `<div style="width:6px;height:6px;background:${color};border-radius:50%;border:1px solid rgba(255,255,255,0.3);"></div>`,
                    iconSize: [6, 6],
                    iconAnchor: [3, 3],
                });

                const marker = L.marker(ll, { icon })
                    .bindPopup(
                        `<div class="text-xs">
                            <div class="font-bold ${p.type === 'kill' ? 'text-red-400' : 'text-blue-400'}">${p.type === 'kill' ? 'Kill' : 'Death'}</div>
                            <div class="text-gray-300">${p.player || 'Unknown'}</div>
                            <div class="text-gray-400">${p.weapon || 'Unknown weapon'}</div>
                            ${p.headshot ? '<div class="text-yellow-400">Headshot</div>' : ''}
                        </div>`,
                        { className: 'kill-popup', closeButton: false }
                    );

                this.markerLayer.addLayer(marker);
            });
        },
    };
}
</script>
@endpush
