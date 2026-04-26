<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Zenner Tasks') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/site-logo-2.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @laravelPWA
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">

            {{-- Logo + Site Name --}}
            <div class="flex flex-col items-center mb-8">
                <a href="/" class="flex items-center gap-3 mb-1">
                    <img src="{{ asset('images/site-logo-2.png') }}" alt="Zenner Tasks" class="h-10 w-auto">
                    <span class="text-2xl font-bold text-gray-900 tracking-tight">Zenner Tasks</span>
                </a>
                <span class="text-sm text-gray-500 tracking-wide">Project Management, Simplified</span>
            </div>

            {{-- Card --}}
            <div class="w-full sm:max-w-md px-8 py-8 bg-white border border-gray-200 shadow-lg overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>

            <p class="mt-8 text-xs text-gray-400">&copy; {{ date('Y') }} Zenner Tasks. All rights reserved.</p>
        </div>
    </body>
</html>
