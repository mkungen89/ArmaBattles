@extends('admin.layout')

@section('title', 'Add Weapon')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.weapons.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Add Weapon</h1>
    </div>

    @if($errors->any())
    <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.weapons.store') }}" method="POST" enctype="multipart/form-data" class="glass-card rounded-xl p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Weapon Name (from game) *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">The exact weapon name as it appears in game data</p>
            </div>

            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-400 mb-2">Display Name</label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">Optional friendly name to show in UI</p>
            </div>

            <div>
                <label for="weapon_type" class="block text-sm font-medium text-gray-400 mb-2">Weapon Type</label>
                <select name="weapon_type" id="weapon_type" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Select type...</option>
                    <option value="rifle" {{ old('weapon_type') === 'rifle' ? 'selected' : '' }}>Rifle</option>
                    <option value="pistol" {{ old('weapon_type') === 'pistol' ? 'selected' : '' }}>Pistol</option>
                    <option value="sniper" {{ old('weapon_type') === 'sniper' ? 'selected' : '' }}>Sniper</option>
                    <option value="shotgun" {{ old('weapon_type') === 'shotgun' ? 'selected' : '' }}>Shotgun</option>
                    <option value="lmg" {{ old('weapon_type') === 'lmg' ? 'selected' : '' }}>LMG</option>
                    <option value="explosive" {{ old('weapon_type') === 'explosive' ? 'selected' : '' }}>Explosive</option>
                    <option value="melee" {{ old('weapon_type') === 'melee' ? 'selected' : '' }}>Melee</option>
                    <option value="vehicle" {{ old('weapon_type') === 'vehicle' ? 'selected' : '' }}>Vehicle Weapon</option>
                    <option value="launcher" {{ old('weapon_type') === 'launcher' ? 'selected' : '' }}>Launcher</option>
                </select>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-400 mb-2">Category</label>
                <select name="category" id="category" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Select category...</option>
                    <option value="primary" {{ old('category') === 'primary' ? 'selected' : '' }}>Primary</option>
                    <option value="secondary" {{ old('category') === 'secondary' ? 'selected' : '' }}>Secondary</option>
                    <option value="equipment" {{ old('category') === 'equipment' ? 'selected' : '' }}>Equipment</option>
                    <option value="special" {{ old('category') === 'special' ? 'selected' : '' }}>Special</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="image" class="block text-sm font-medium text-gray-400 mb-2">Weapon Image</label>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="file" name="image" id="image" accept="image/png,image/jpeg,image/gif,image/webp" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-green-600 file:text-white hover:file:bg-green-500">
                        <p class="text-xs text-gray-500 mt-1">Recommended: 256 x 256 px, transparent PNG. Max 2 MB.</p>
                    </div>
                    <div id="imagePreview" class="hidden w-24 h-24 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden">
                        <img id="previewImg" src="" alt="Preview" class="max-w-full max-h-full object-contain">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Create Weapon
            </button>
            <a href="{{ route('admin.weapons.index') }}" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('imagePreview').classList.add('flex');
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
