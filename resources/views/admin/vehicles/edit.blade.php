@extends('admin.layout')

@section('title', 'Edit Vehicle - ' . $vehicle->name)

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.vehicles.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit Vehicle</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data" class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Vehicle Name (from game) *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $vehicle->name) }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">The exact vehicle name as it appears in game data</p>
            </div>

            <div>
                <label for="display_name" class="block text-sm font-medium text-gray-400 mb-2">Display Name</label>
                <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $vehicle->display_name) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">Optional friendly name to show in UI</p>
            </div>

            <div>
                <label for="vehicle_type" class="block text-sm font-medium text-gray-400 mb-2">Vehicle Type</label>
                <select name="vehicle_type" id="vehicle_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Select type...</option>
                    <option value="Car" {{ old('vehicle_type', $vehicle->vehicle_type) === 'Car' ? 'selected' : '' }}>Car</option>
                    <option value="Truck" {{ old('vehicle_type', $vehicle->vehicle_type) === 'Truck' ? 'selected' : '' }}>Truck</option>
                    <option value="APC" {{ old('vehicle_type', $vehicle->vehicle_type) === 'APC' ? 'selected' : '' }}>APC</option>
                    <option value="IFV" {{ old('vehicle_type', $vehicle->vehicle_type) === 'IFV' ? 'selected' : '' }}>IFV</option>
                    <option value="Tank" {{ old('vehicle_type', $vehicle->vehicle_type) === 'Tank' ? 'selected' : '' }}>Tank</option>
                    <option value="Helicopter" {{ old('vehicle_type', $vehicle->vehicle_type) === 'Helicopter' ? 'selected' : '' }}>Helicopter</option>
                    <option value="Boat" {{ old('vehicle_type', $vehicle->vehicle_type) === 'Boat' ? 'selected' : '' }}>Boat</option>
                    <option value="Other" {{ old('vehicle_type', $vehicle->vehicle_type) === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-400 mb-2">Current Image</label>
                @if($vehicle->image_path)
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-32 h-32 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden">
                        <img src="{{ Storage::url($vehicle->image_path) }}" alt="{{ $vehicle->display_name }}" class="max-w-full max-h-full object-contain">
                    </div>
                    <form action="{{ route('admin.vehicles.delete-image', $vehicle) }}" method="POST" onsubmit="return confirm('Remove this image?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm rounded transition">Remove Image</button>
                    </form>
                </div>
                @else
                <p class="text-gray-500 text-sm mb-4">No image uploaded</p>
                @endif

                <label for="image" class="block text-sm font-medium text-gray-400 mb-2">{{ $vehicle->image_path ? 'Replace Image' : 'Upload Image' }}</label>
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="file" name="image" id="image" accept="image/png,image/jpeg,image/gif,image/webp" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-green-600 file:text-white hover:file:bg-green-500">
                        <p class="text-xs text-gray-500 mt-1">Recommended: 256 x 256 px, transparent PNG. Max 2 MB.</p>
                    </div>
                    <div id="imagePreview" class="hidden w-24 h-24 bg-gray-900 rounded-lg flex items-center justify-center overflow-hidden">
                        <img id="previewImg" src="" alt="Preview" class="max-w-full max-h-full object-contain">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">Save Changes</button>
            <a href="{{ route('admin.vehicles.index') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">Cancel</a>
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
