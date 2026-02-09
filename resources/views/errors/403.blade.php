@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <h1 class="text-8xl font-bold text-green-500">403</h1>
        <p class="mt-4 text-2xl font-semibold text-white">Access Denied</p>
        <p class="mt-2 text-gray-400">You do not have permission to access this page.</p>
        <div class="mt-8">
            <a href="{{ route('home') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition">
                Go Home
            </a>
        </div>
    </div>
</div>
@endsection
