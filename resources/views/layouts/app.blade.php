<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ClassBridge AI') - ClassBridge AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%230f172a'/><text x='50' y='59' text-anchor='middle' font-size='34' font-family='Arial, sans-serif' font-weight='700' fill='white'>CB</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_32%),radial-gradient(circle_at_top_right,_rgba(99,102,241,0.12),_transparent_28%),linear-gradient(180deg,_#f8fafc_0%,_#eff6ff_55%,_#f8fafc_100%)] text-slate-900 antialiased">
    <div class="min-h-screen">
        <nav class="border-b border-white/60 bg-white/75 shadow-[0_1px_0_rgba(15,23,42,0.04)] backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ Auth::check() ? route('dashboard') : route('home') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black tracking-[0.2em] text-white shadow-lg shadow-slate-950/20">CB</span>
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">ClassBridge AI</div>
                        <div class="text-lg font-bold text-slate-900">Protected live teaching workspace</div>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    @auth
                        <x-secondary-button href="{{ route('dashboard') }}" class="hidden sm:inline-flex">
                            Go to workspace
                        </x-secondary-button>
                        <x-status-badge tone="success" class="hidden sm:inline-flex">
                            {{ classbridge_role_label(Auth::user()->role?->slug) }}
                        </x-status-badge>
                        <span class="hidden text-sm font-medium text-slate-700 md:inline">{{ Auth::user()->displayName() }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-primary-button type="submit">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign out
                            </x-primary-button>
                        </form>
                    @else
                        <x-secondary-button href="{{ route('demo.live-classroom') }}" class="hidden md:inline-flex">
                            Try Demo Classroom
                        </x-secondary-button>
                        <x-secondary-button href="{{ route('home') }}#request-demo" class="hidden md:inline-flex">
                            Request Demo
                        </x-secondary-button>
                        <x-secondary-button href="{{ route('login') }}">
                            Login
                        </x-secondary-button>
                        <x-primary-button href="{{ route('register') }}">
                            Start Free Trial
                        </x-primary-button>
                    @endauth
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </div>

    @if (session('success'))
        <div class="fixed bottom-4 right-4 rounded-xl bg-emerald-600 px-4 py-2 text-white shadow-xl shadow-emerald-900/20" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="fixed bottom-4 right-4 rounded-xl bg-rose-600 px-4 py-2 text-white shadow-xl shadow-rose-900/20" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
            {{ session('error') }}
        </div>
    @endif

    @stack('scripts')
</body>
</html>
