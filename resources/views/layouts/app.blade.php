<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Zenner Tasks') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/site-logo-2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    (['resources/css/app.css', 'resources/js/app.js'])
    @laravelPWA
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js" defer></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    {{-- ── Mobile backdrop ─────────────────────────────────────────── --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-black/60 lg:hidden"
        style="display:none"
    ></div>

    {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-gray-900 text-gray-100 flex-shrink-0 overflow-y-auto transform transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 lg:z-auto">

        {{-- Mobile close button --}}
        <button
            @click="sidebarOpen = false"
            class="absolute top-3 right-3 text-gray-400 hover:text-white lg:hidden"
            aria-label="Close menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Logo --}}
        <div class="flex flex-col px-5 py-5 border-b border-gray-700 gap-1">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/site-logo-2.png') }}" alt="Zenner Tasks" class="h-6 w-auto">
                <span class="font-semibold text-white text-lg tracking-tight">Zenner Tasks</span>
            </div>
            <span class="text-gray-400 tracking-wide" style="font-size:12px">Project Management, Simplified</span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                      {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            {{-- Projects --}}
            <div class="pt-3">
                <div class="flex items-center justify-between px-3 mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Projects</span>
                    @can('create', App\Models\Project::class)
                    <a href="{{ route('projects.create') }}" class="text-gray-400 hover:text-white" title="New Project">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>
                    @endcan
                </div>

                @php
                    $sidebarProjects = App\Models\Project::query()->forUser(auth()->user())
                        ->active()->orderBy('name')->get();
                @endphp

                @foreach($sidebarProjects as $sidebarProject)
                <a href="{{ route('projects.show', $sidebarProject) }}"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition
                          {{ request()->route('project')?->id === $sidebarProject->id ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $sidebarProject->color ?? '#6366f1' }}"></span>
                    <span class="truncate">{{ $sidebarProject->name }}</span>
                </a>
                @endforeach

                <a href="{{ route('projects.index') }}"
                   class="flex items-center gap-2 px-3 py-1.5 mt-1 rounded-lg text-xs text-gray-500 hover:text-gray-300 transition">
                    All projects &rarr;
                </a>
            </div>
        </nav>

        {{-- Bottom: notifications + user --}}
        <div class="border-t border-gray-700 px-3 py-3 space-y-1" x-data="{ userOpen: false }">
            {{-- Notification bell --}}
            <a href="{{ route('notifications.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                <div class="relative">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unread > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                </div>
                Notifications
            </a>

            {{-- User dropdown --}}
            <div class="relative">
                <button @click="userOpen = !userOpen"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                    <span class="truncate flex-1 text-left">{{ auth()->user()->name }}</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="userOpen" @click.outside="userOpen = false" x-transition
                     class="absolute bottom-full left-0 mb-1 w-full bg-gray-800 border border-gray-700 rounded-lg shadow-lg overflow-hidden z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Admin: Users</a>
                    @endif
                    <div class="border-t border-gray-700"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-700">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Main content ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- ── Mobile top bar ──────────────────────────────────────── --}}
        <header class="flex items-center gap-3 px-4 py-3 bg-gray-900 text-white lg:hidden flex-shrink-0">
            <button @click="sidebarOpen = true" aria-label="Open menu" class="text-gray-300 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <img src="{{ asset('images/site-logo-2.png') }}" alt="Zenner Tasks" class="h-5 w-auto">
            <span class="font-semibold text-sm tracking-tight">Zenner Tasks</span>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error'))
        <div class="px-6 pt-4">
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="flex items-center justify-between bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 hover:text-green-800 ml-4">&#x2715;</button>
            </div>
            @endif
            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
                 class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-600 hover:text-red-800 ml-4">&#x2715;</button>
            </div>
            @endif
        </div>
        @endif

        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
