<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black tracking-[0.2em] text-white shadow-lg shadow-slate-950/20">CB</span>
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">ClassBridge AI</div>
                        <div class="text-lg font-bold text-slate-900">Protected live teaching workspace</div>
                    </div>
                </a>

                <div class="flex items-center gap-3">
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
                </div>
            </div>
        </nav>

        <main>
            @if (session('success') || session('error'))
                <div class="mx-auto max-w-7xl px-5 pt-6 lg:px-8">
                    @if (session('success'))
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
