@php
    $isClassroomWorkspace = isset($classroom) && request()->routeIs('classrooms.show');
    $lessonTitle = $isClassroomWorkspace
        ? ($classroom->title ?? 'Live Classroom')
        : trim($__env->yieldContent('title', 'Dashboard'));
@endphp

@if ($isClassroomWorkspace)
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-[0_1px_0_rgba(15,23,42,0.04)] backdrop-blur-xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:text-slate-900 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                    :aria-label="sidebarOpen ? 'Collapse sidebar' : 'Open sidebar'"
                >
                    <svg x-show="!sidebarOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="sidebarOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black tracking-[0.2em] text-white shadow-lg shadow-slate-950/20">CB</span>
                    <div class="text-sm font-bold tracking-tight text-slate-900">ClassBridge AI</div>
                </a>

                <span class="hidden h-8 w-px bg-slate-200 sm:block"></span>

                <div class="min-w-0 flex items-center gap-3">
                    <h1 class="truncate text-lg font-black tracking-tight text-slate-900 sm:text-xl">{{ $lessonTitle }}</h1>
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                        Protected workspace
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="relative inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                    aria-label="Notifications"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C8.67 6.165 7 8.388 7 11v3.159c0 .538-.214 1.055-.595 1.436L5 17h5m5 0a3 3 0 11-6 0m6 0H9"/>
                    </svg>
                    <span class="absolute right-1 top-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-4 text-white">3</span>
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-primary-button type="submit" class="px-4 py-2.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </x-primary-button>
                </form>
            </div>
        </div>
    </header>
@else
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 px-4 py-4 shadow-[0_1px_0_rgba(15,23,42,0.04)] backdrop-blur-xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-4">
                <button
                    type="button"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:text-slate-900"
                    @click="sidebarOpen = !sidebarOpen"
                    :aria-label="sidebarOpen ? 'Collapse sidebar' : 'Open sidebar'"
                >
                    <svg x-show="!sidebarOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="sidebarOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>

                <div class="min-w-0 flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black tracking-[0.2em] text-white shadow-lg shadow-slate-950/20">CB</span>
                        <div class="text-sm font-bold tracking-tight text-slate-900">ClassBridge AI</div>
                    </a>
                    <span class="hidden h-8 w-px bg-slate-200 sm:block"></span>
                    <h1 class="truncate text-lg font-black tracking-tight text-slate-900 sm:text-xl">@yield('title', 'Dashboard')</h1>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-secondary-button type="button" class="px-4 py-2 text-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-slate-400 opacity-30"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-500"></span>
                    </span>
                    <span>Notifications</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Soon</span>
                </x-secondary-button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-primary-button type="submit">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </x-primary-button>
                </form>
            </div>
        </div>
    </header>
@endif
