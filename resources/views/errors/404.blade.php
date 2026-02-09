@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <h1 class="text-8xl font-bold text-green-500">404</h1>
        <p class="mt-4 text-2xl font-semibold text-white">Page Not Found</p>
        <p class="mt-2 text-gray-400">The page you are looking for does not exist or has been moved.</p>
        <div class="mt-8 flex items-center justify-center space-x-4">
            <a href="{{ url()->previous() }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition">
                Go Back
            </a>
            <a href="{{ route('home') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition">
                Go Home
            </a>
        </div>
    </div>
</div>
@endsection
