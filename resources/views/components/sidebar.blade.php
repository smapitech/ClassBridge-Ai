<!-- Sidebar -->
@php
    $user = Auth::user();
    $roleSlug = $user->role?->slug;
    $isPrivateTutorWorkspace = $user->school?->isPrivateTutorWorkspace() ?? false;
    $menuPartial = $roleSlug === 'school_owner' && $isPrivateTutorWorkspace
        ? 'layouts.sidebars.private_tutor'
        : 'layouts.sidebars.' . ($roleSlug ?? 'default');
@endphp

<aside
    x-show="sidebarOpen"
    x-cloak
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-950/10 transition-transform lg:static lg:z-auto lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-4 py-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black tracking-[0.2em] text-white shadow-lg shadow-slate-950/20">CB</span>
            <div class="text-sm font-bold text-slate-900">ClassBridge AI</div>
        </a>

        <button type="button" class="rounded-full border border-slate-200 p-2 text-slate-500 transition hover:border-slate-300 hover:text-slate-900 lg:hidden" @click="sidebarOpen = false" aria-label="Close sidebar">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="px-4 pt-4">
        <a href="{{ route('live-lessons.create') }}" class="flex items-center gap-3 rounded-2xl bg-slate-950 px-4 py-3 text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:shadow-xl">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
            <span class="text-sm font-semibold">Start Live Lesson</span>
        </a>
    </div>

    <nav class="flex-1 space-y-5 overflow-y-auto px-4 py-5">
        @include($menuPartial)
    </nav>

    <div class="border-t border-slate-200 p-4">
        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->displayName(), 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-slate-400">Signed in as</p>
                    <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->displayName() }}</p>
                    <p class="text-xs font-medium text-slate-500">{{ classbridge_role_label(Auth::user()->role?->slug) }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>
