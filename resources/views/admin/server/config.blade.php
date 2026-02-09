@extends('admin.layout')

@section('title', 'Server Config')

@section('admin-content')
<div x-data="configEditor()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Server Configuration</h1>
            <p class="text-sm text-gray-500 mt-1">Edit Arma server and stats collector settings</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Dashboard</a>
            <a href="{{ route('admin.server.players') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Players</a>
            <a href="{{ route('admin.server.logs') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Logs</a>
            <a href="{{ route('admin.server.mods') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Mods</a>
        </div>
    </div>

    {{-- Notice --}}
    <div class="p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm text-yellow-400">Restart the server to apply config changes. Redacted password fields will keep their existing values.</p>
    </div>

    {{-- Config Tabs --}}
    <div class="flex items-center gap-1 bg-gray-800/50 border border-gray-700 rounded-xl p-1.5">
        <button @click="activeTab = 'arma'" :class="activeTab === 'arma' ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white border-transparent'"
                class="flex-1 text-center px-4 py-2 rounded-lg text-sm font-medium transition border">
            Arma Server Config
        </button>
        <button @click="activeTab = 'stats'" :class="activeTab === 'stats' ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white border-transparent'"
                class="flex-1 text-center px-4 py-2 rounded-lg text-sm font-medium transition border">
            Stats Collector Config
        </button>
    </div>

    {{-- Arma Config Tab --}}
    <div x-show="activeTab === 'arma'" x-cloak>
        @if(!$armaConfig)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-red-500/50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-gray-400">Could not load Arma config from game server</p>
        </div>
        @else
        @php $config = $armaConfig['config'] ?? []; @endphp
        <form method="POST" action="{{ route('admin.server.config.arma.update') }}" x-ref="armaForm"
              @submit.prevent="submitArmaConfig()">
            @csrf
            @method('PUT')
            <input type="hidden" name="config_json" x-ref="armaConfigJson">

            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Common Settings --}}
                <div class="space-y-6">
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h3 class="text-md font-semibold text-white mb-4">Server Settings</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Server Name</label>
                                <input type="text" x-model="armaConfig.game.name"
                                       class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Max Players</label>
                                    <input type="number" x-model.number="armaConfig.game.maxPlayers" min="1" max="256"
                                           class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Visible</label>
                                    <div class="flex items-center h-[38px]">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" x-model="armaConfig.game.visible" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Scenario ID</label>
                                <input type="text" x-model="armaConfig.game.scenarioId"
                                       class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white font-mono text-xs focus:outline-none focus:border-green-500/50">
                            </div>
                        </div>
                    </div>

                    {{-- Game Properties --}}
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h3 class="text-md font-semibold text-white mb-4">Game Properties</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Max View Distance</label>
                                    <input type="number" x-model.number="armaConfig.game.gameProperties.serverMaxViewDistance" min="500" max="10000"
                                           class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Network View Distance</label>
                                    <input type="number" x-model.number="armaConfig.game.gameProperties.networkViewDistance" min="500" max="10000"
                                           class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Min Grass Distance</label>
                                <input type="number" x-model.number="armaConfig.game.gameProperties.serverMinGrassDistance" min="0" max="200"
                                       class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Disable 3rd Person</label>
                                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                                        <input type="checkbox" x-model="armaConfig.game.gameProperties.disableThirdPerson" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">BattlEye</label>
                                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                                        <input type="checkbox" x-model="armaConfig.game.gameProperties.battlEye" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1">Fast Validation</label>
                                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                                        <input type="checkbox" x-model="armaConfig.game.gameProperties.fastValidation" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>
                            </div>

                            {{-- Platforms --}}
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Supported Platforms</label>
                                <div class="flex flex-wrap gap-3">
                                    <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                        <input type="checkbox" value="PLATFORM_PC"
                                               :checked="armaConfig.game.supportedPlatforms?.includes('PLATFORM_PC')"
                                               @change="togglePlatform('PLATFORM_PC', $event.target.checked)"
                                               class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                        PC
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                        <input type="checkbox" value="PLATFORM_XBL"
                                               :checked="armaConfig.game.supportedPlatforms?.includes('PLATFORM_XBL')"
                                               @change="togglePlatform('PLATFORM_XBL', $event.target.checked)"
                                               class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                        Xbox
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                        <input type="checkbox" value="PLATFORM_PSN"
                                               :checked="armaConfig.game.supportedPlatforms?.includes('PLATFORM_PSN')"
                                               @change="togglePlatform('PLATFORM_PSN', $event.target.checked)"
                                               class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                        PlayStation
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Network + Advanced --}}
                <div class="space-y-6">
                    {{-- Network Info (read-only) --}}
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h3 class="text-md font-semibold text-white mb-4">Network</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Bind Address</span>
                                <code class="text-xs text-gray-300">{{ $config['bindAddress'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Bind Port</span>
                                <code class="text-xs text-gray-300">{{ $config['bindPort'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Public Address</span>
                                <code class="text-xs text-gray-300">{{ $config['publicAddress'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">A2S Port</span>
                                <code class="text-xs text-gray-300">{{ $config['a2s']['port'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">RCON Port</span>
                                <code class="text-xs text-gray-300">{{ $config['rcon']['port'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Admin List</span>
                                <span class="text-xs text-gray-400">{{ count($config['game']['admins'] ?? []) }} admin(s)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Advanced JSON Editor --}}
                    <div x-data="{ showAdvanced: false }" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <div class="flex items-center justify-between cursor-pointer" @click="showAdvanced = !showAdvanced">
                            <h3 class="text-md font-semibold text-white">Advanced JSON Editor</h3>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="showAdvanced && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="showAdvanced" x-collapse class="mt-4">
                            <p class="text-xs text-gray-500 mb-2">Edit the raw JSON config. This will be used instead of the form fields above.</p>
                            <textarea x-model="armaJsonRaw" rows="20"
                                      class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-xs text-gray-300 font-mono focus:outline-none focus:border-green-500/50 resize-y"></textarea>
                            <button type="button" @click="parseJsonToForm()" class="mt-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-xs transition">
                                Apply JSON to Form
                            </button>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <button type="submit" :disabled="armaSaving"
                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-medium transition disabled:opacity-50 flex items-center justify-center gap-2">
                        <svg x-show="armaSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="armaSaving ? 'Saving...' : 'Save Arma Config'"></span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>

    {{-- Stats Config Tab --}}
    <div x-show="activeTab === 'stats'" x-cloak>
        @if(!$statsConfig)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-red-500/50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-gray-400">Could not load stats config from game server</p>
        </div>
        @else
        @php $sConfig = $statsConfig['config'] ?? []; @endphp
        <form method="POST" action="{{ route('admin.server.config.stats.update') }}" x-ref="statsForm"
              @submit.prevent="submitStatsConfig()">
            @csrf
            @method('PUT')
            <input type="hidden" name="config_json" x-ref="statsConfigJson">

            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Server Settings --}}
                <div class="space-y-6">
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h3 class="text-md font-semibold text-white mb-4">Server Connection</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Server Name</span>
                                <span class="text-white">{{ $sConfig['server']['name'] ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Host</span>
                                <code class="text-xs text-gray-300">{{ $sConfig['server']['host'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Query Port</span>
                                <code class="text-xs text-gray-300">{{ $sConfig['server']['queryPort'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">RCON Port</span>
                                <code class="text-xs text-gray-300">{{ $sConfig['server']['rconPort'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Log Reader Mode</span>
                                <code class="text-xs text-gray-300">{{ $sConfig['server']['logReaderMode'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Log Directory</span>
                                <code class="text-xs text-gray-300 truncate max-w-[200px]" title="{{ $sConfig['server']['logDir'] ?? '' }}">{{ $sConfig['server']['logDir'] ?? '—' }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h3 class="text-md font-semibold text-white mb-4">API Settings</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">API Enabled</span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ ($sConfig['api']['enabled'] ?? false) ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ ($sConfig['api']['enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Base URL</span>
                                <code class="text-xs text-gray-300 truncate max-w-[200px]">{{ $sConfig['api']['baseUrl'] ?? '—' }}</code>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Database Enabled</span>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ ($sConfig['database']['enabled'] ?? false) ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ ($sConfig['database']['enabled'] ?? false) ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- Plugins --}}
                    @if(!empty($sConfig['plugins']))
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h3 class="text-md font-semibold text-white mb-4">Plugins</h3>
                        <div class="space-y-3">
                            @foreach($sConfig['plugins'] as $i => $plugin)
                            <div class="flex items-center justify-between p-3 bg-gray-900/30 rounded-lg">
                                <div>
                                    <span class="text-sm text-white font-medium">{{ $plugin['name'] ?? 'Plugin ' . ($i+1) }}</span>
                                    @if(isset($plugin['type']))
                                    <span class="ml-2 text-xs text-gray-500">{{ $plugin['type'] }}</span>
                                    @endif
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           :checked="statsConfig.plugins[{{ $i }}]?.enabled ?? {{ json_encode($plugin['enabled'] ?? true) }}"
                                           @change="statsConfig.plugins[{{ $i }}].enabled = $event.target.checked"
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Advanced JSON --}}
                    <div x-data="{ showAdvanced: false }" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <div class="flex items-center justify-between cursor-pointer" @click="showAdvanced = !showAdvanced">
                            <h3 class="text-md font-semibold text-white">Advanced JSON Editor</h3>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="showAdvanced && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <div x-show="showAdvanced" x-collapse class="mt-4">
                            <p class="text-xs text-gray-500 mb-2">Edit the raw JSON config directly.</p>
                            <textarea x-model="statsJsonRaw" rows="20"
                                      class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-xs text-gray-300 font-mono focus:outline-none focus:border-green-500/50 resize-y"></textarea>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <button type="submit" :disabled="statsSaving"
                            class="w-full px-4 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-medium transition disabled:opacity-50 flex items-center justify-center gap-2">
                        <svg x-show="statsSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="statsSaving ? 'Saving...' : 'Save Stats Config'"></span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>

@push('scripts')
<script>
function configEditor() {
    const rawArma = {!! json_encode(($armaConfig['config'] ?? null)) !!};
    const rawStats = {!! json_encode(($statsConfig['config'] ?? null)) !!};

    return {
        activeTab: 'arma',
        armaSaving: false,
        statsSaving: false,

        // Deep clone so form edits don't mutate the raw object
        armaConfig: rawArma ? JSON.parse(JSON.stringify(rawArma)) : { game: { gameProperties: {}, supportedPlatforms: [] } },
        armaJsonRaw: rawArma ? JSON.stringify(rawArma, null, 2) : '{}',

        statsConfig: rawStats ? JSON.parse(JSON.stringify(rawStats)) : {},
        statsJsonRaw: rawStats ? JSON.stringify(rawStats, null, 2) : '{}',

        togglePlatform(platform, checked) {
            if (!this.armaConfig.game.supportedPlatforms) {
                this.armaConfig.game.supportedPlatforms = [];
            }
            if (checked) {
                if (!this.armaConfig.game.supportedPlatforms.includes(platform)) {
                    this.armaConfig.game.supportedPlatforms.push(platform);
                }
            } else {
                this.armaConfig.game.supportedPlatforms = this.armaConfig.game.supportedPlatforms.filter(p => p !== platform);
            }
        },

        parseJsonToForm() {
            try {
                this.armaConfig = JSON.parse(this.armaJsonRaw);
            } catch (e) {
                alert('Invalid JSON: ' + e.message);
            }
        },

        submitArmaConfig() {
            // Sync form values to JSON, preserving any advanced edits
            const config = JSON.parse(JSON.stringify(this.armaConfig));
            this.$refs.armaConfigJson.value = JSON.stringify(config);
            this.armaSaving = true;
            this.$refs.armaForm.submit();
        },

        submitStatsConfig() {
            // Try parsing raw JSON if it was edited, otherwise use the object
            let config;
            try {
                config = JSON.parse(this.statsJsonRaw);
                // Merge plugin toggles
                if (this.statsConfig.plugins) {
                    config.plugins = this.statsConfig.plugins;
                }
            } catch {
                config = JSON.parse(JSON.stringify(this.statsConfig));
            }
            this.$refs.statsConfigJson.value = JSON.stringify(config);
            this.statsSaving = true;
            this.$refs.statsForm.submit();
        }
    };
}
</script>
@endpush
@endsection
