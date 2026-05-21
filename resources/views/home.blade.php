<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Zenner Tasks') }} — Project Management, Simplified</title>
    <meta name="description" content="Zenner Tasks is a collaborative project management platform. Organize work, assign tasks, track progress, and communicate with your team — all in one place.">
    <link rel="icon" type="image/png" href="{{ asset('images/site-logo-2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @laravelPWA
</head>
<body class="font-sans antialiased bg-white text-gray-900">

{{-- ═══════════════════════════════════════════
     NAV
═══════════════════════════════════════════ --}}
<header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2.5">
            <img src="{{ asset('images/site-logo-2.png') }}" alt="Zenner Tasks" class="h-7 w-auto">
            <span class="font-bold text-white text-lg tracking-tight">Zenner Tasks</span>
        </a>

        {{-- Nav links --}}
        <nav class="flex items-center gap-3">
            @if (Route::has('login'))
                <a href="{{ route('login') }}"
                   class="text-sm text-gray-300 hover:text-white transition px-4 py-1.5 rounded-md hover:bg-gray-700">
                    Sign In
                </a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="text-sm font-semibold bg-purple-600 hover:bg-purple-500 transition text-white px-4 py-1.5 rounded-md">
                    Get Started Free
                </a>
            @endif
        </nav>
    </div>
</header>

{{-- ═══════════════════════════════════════════
     HERO + LOGIN
═══════════════════════════════════════════ --}}
<section class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 lg:gap-20 items-center">

            {{-- ── Left: headline ─────────────────────────── --}}
            <div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-purple-400 bg-purple-900/40 border border-purple-700/50 px-3 py-1 rounded-full mb-6">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Built for Modern Teams
                </span>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight mb-6">
                    Project Management,
                    <span class="text-purple-400">Simplified.</span>
                </h1>

                <p class="text-lg text-gray-400 leading-relaxed mb-8 max-w-lg">
                    Zenner Tasks helps teams stay organised, hit deadlines, and ship great work. Manage projects, assign tasks, track progress — all in one clean workspace.
                </p>

                {{-- Feature bullets --}}
                <ul class="space-y-3 mb-10">
                    @foreach ([
                        ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'text' => 'Kanban boards & list views per project'],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'text' => 'Role-based access for admins, members & clients'],
                        ['icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'text' => 'Threaded comments with @mentions & attachments'],
                        ['icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'text' => 'Real-time email notifications on every update'],
                    ] as $feature)
                    <li class="flex items-start gap-3 text-gray-300 text-sm">
                        <span class="flex-shrink-0 mt-0.5 w-5 h-5 rounded-full bg-purple-900/60 border border-purple-700/50 flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                            </svg>
                        </span>
                        {{ $feature['text'] }}
                    </li>
                    @endforeach
                </ul>

                {{-- Social proof --}}
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex -space-x-2">
                        @foreach (['bg-purple-500','bg-indigo-500','bg-pink-500','bg-amber-500'] as $color)
                        <div class="w-8 h-8 rounded-full {{ $color }} border-2 border-gray-900 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        @endforeach
                    </div>
                    <span>Trusted by teams worldwide</span>
                </div>
            </div>

            {{-- ── Right: login card ───────────────────────── --}}
            <div>
                <div class="bg-white rounded-2xl shadow-2xl shadow-black/40 border border-gray-100 p-8">

                    <div class="flex flex-col items-center mb-7">
                        <img src="{{ asset('images/site-logo-2.png') }}" alt="" class="h-10 w-auto mb-3">
                        <h2 class="text-xl font-bold text-gray-900">Sign in to your account</h2>
                        <p class="text-sm text-gray-500 mt-1">Welcome back! Enter your details below.</p>
                    </div>

                    {{-- Session status --}}
                    @if (session('status'))
                        <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        {{-- Email --}}
                        <div class="mb-4">
                            <label for="home_email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                            <input
                                id="home_email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@example.com"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400
                                       focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent
                                       @error('email') border-red-400 bg-red-50 @enderror"
                            >
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <label for="home_password" class="block text-sm font-medium text-gray-700">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs text-purple-600 hover:text-purple-800 hover:underline">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            <input
                                id="home_password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400
                                       focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent
                                       @error('password') border-red-400 bg-red-50 @enderror"
                            >
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remember me --}}
                        <div class="flex items-center mb-6">
                            <input id="home_remember" type="checkbox" name="remember"
                                   class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500 w-4 h-4">
                            <label for="home_remember" class="ms-2 text-sm text-gray-600">Remember me</label>
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 transition text-white font-semibold text-sm py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Sign In
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </button>
                    </form>

                    {{-- Divider --}}
                    <div class="relative my-5">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-3 bg-white text-gray-400">or continue with</span>
                        </div>
                    </div>

                    {{-- Google OAuth --}}
                    @if (Route::has('auth.google'))
                    <a href="{{ route('auth.google') }}"
                       class="flex items-center justify-center gap-3 w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Continue with Google
                    </a>
                    @endif

                    {{-- Register link --}}
                    @if (Route::has('register'))
                    <p class="mt-6 text-center text-sm text-gray-500">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-semibold text-purple-600 hover:text-purple-800 hover:underline">
                            Create one free
                        </a>
                    </p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     FEATURES
═══════════════════════════════════════════ --}}
<section class="bg-gray-50 border-t border-gray-200 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Everything your team needs</h2>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">From project kick-off to delivery, Zenner Tasks gives every team member the clarity they need to do great work.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Feature cards --}}
            @foreach ([
                [
                    'color'  => 'purple',
                    'icon'   => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
                    'title'  => 'Kanban & List Views',
                    'desc'   => 'Visualise work as cards on a drag-and-drop Kanban board or a compact sortable list — whichever fits your workflow.',
                ],
                [
                    'color'  => 'indigo',
                    'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'title'  => 'Custom Status Workflows',
                    'desc'   => 'Define status columns per project — Backlog, In Progress, Review, Done, or anything your process demands.',
                ],
                [
                    'color'  => 'pink',
                    'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'title'  => 'Role-Based Access Control',
                    'desc'   => 'Super Admins, Admins, Members, and Clients each see exactly what they need — no more, no less.',
                ],
                [
                    'color'  => 'amber',
                    'icon'   => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                    'title'  => 'Threaded Comments',
                    'desc'   => 'Keep all communication in context. @mention teammates, attach files, and maintain a full audit trail per task.',
                ],
                [
                    'color'  => 'emerald',
                    'icon'   => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                    'title'  => 'Smart Notifications',
                    'desc'   => 'Get emailed when tasks are assigned, due dates change, priorities shift, or someone mentions you in a comment.',
                ],
                [
                    'color'  => 'sky',
                    'icon'   => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'title'  => 'Activity Dashboard',
                    'desc'   => 'See overdue tasks, upcoming deadlines, and recently updated items at a glance the moment you log in.',
                ],
            ] as $feature)
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-10 h-10 rounded-lg bg-{{ $feature['color'] }}-100 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-{{ $feature['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1.5">{{ $feature['title'] }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA BANNER
═══════════════════════════════════════════ --}}
<section class="bg-purple-600 py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-white tracking-tight mb-4">Ready to get organised?</h2>
        <p class="text-purple-100 text-lg mb-8">
            Join your team on Zenner Tasks. It's free to get started.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            @if (Route::has('register'))
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-purple-700 font-semibold text-sm rounded-lg hover:bg-purple-50 transition shadow-sm">
                Create your free account
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            @endif
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-purple-700/60 hover:bg-purple-700 text-white font-medium text-sm rounded-lg transition border border-purple-500">
                Already have an account? Sign in
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ --}}
<footer class="bg-gray-900 text-gray-400 border-t border-gray-800 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/site-logo-2.png') }}" alt="Zenner Tasks" class="h-6 w-auto opacity-70">
            <span class="text-sm font-medium text-gray-300">Zenner Tasks</span>
        </div>
        <p class="text-xs text-gray-500 text-center">
            &copy; {{ date('Y') }} Zenner Tasks. All rights reserved.
        </p>
        <div class="flex items-center gap-5 text-xs">
            <a href="{{ route('login') }}" class="hover:text-gray-200 transition">Sign In</a>
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="hover:text-gray-200 transition">Register</a>
            @endif
        </div>
    </div>
</footer>

</body>
</html>
