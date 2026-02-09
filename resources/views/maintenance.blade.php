<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - {{ site_setting('site_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-900 text-white flex items-center justify-center">
    <div class="text-center max-w-lg px-6">
        <svg class="w-20 h-20 text-yellow-500 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <h1 class="text-3xl font-bold mb-4">Under Maintenance</h1>
        <p class="text-gray-400 text-lg mb-8">{{ $message }}</p>
        <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-400 transition">Admin Login</a>
    </div>
</body>
</html>
