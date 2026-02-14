@extends('layouts.app')

@section('title', 'Create Recruitment Listing')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-white mb-6">Create Recruitment Listing</h1>

    <div class="bg-gray-800 rounded-lg p-6">
        <form action="{{ route('recruitment.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">About You</label>
                <textarea name="message" rows="6" required
                          class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                          placeholder="Tell teams about your experience, playstyle, and what you're looking for... (minimum 50 characters)">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Playstyle</label>
                    <select name="playstyle" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white">
                        <option value="">Select...</option>
                        <option value="casual">Casual</option>
                        <option value="competitive">Competitive</option>
                        <option value="milsim">MilSim</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Region</label>
                    <select name="region" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white">
                        <option value="">Select...</option>
                        <option value="NA">North America</option>
                        <option value="EU">Europe</option>
                        <option value="APAC">Asia-Pacific</option>
                        <option value="SA">South America</option>
                        <option value="OCE">Oceania</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Availability</label>
                <select name="availability" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white">
                    <option value="">Select...</option>
                    <option value="weekdays">Weekdays</option>
                    <option value="weekends">Weekends</option>
                    <option value="both">Both</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Preferred Roles (Optional)</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-2 p-3 bg-gray-900 rounded-lg hover:bg-gray-850 cursor-pointer">
                            <input type="checkbox" name="preferred_roles[]" value="{{ $role->id }}" class="w-4 h-4 text-green-600">
                            <i data-lucide="{{ $role->icon }}" class="w-4 h-4"></i>
                            <span class="text-sm text-white">{{ $role->display_name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-blue-900/20 border border-blue-900 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-300"><strong>Note:</strong> Your listing will be active for 30 days and visible to all teams.</p>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('recruitment.index') }}" class="text-gray-400 hover:text-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                    Create Listing
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
