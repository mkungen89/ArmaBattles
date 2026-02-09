@extends('layouts.app')

@section('title', 'Server Error')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <h1 class="text-8xl font-bold text-green-500">500</h1>
        <p class="mt-4 text-2xl font-semibold text-white">Server Error</p>
        <p class="mt-2 text-gray-400">Something went wrong. Please try again later.</p>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition">
                Go Home
            </a>
        </div>
    </div>
</div>
@endsection
