@props([
    'href' => null,
    'title' => null,
    'description' => null,
    'badge' => null,
    'tone' => 'neutral',
])

@php
    $toneClasses = match ($tone) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100',
        'purple' => 'bg-violet-50 text-violet-700 border-violet-200 hover:bg-violet-100',
        default => 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100',
    };
@endphp

<a href="{{ $href ?? '#' }}" {{ $attributes->class(['group cb-quick-action block h-full', $toneClasses]) }}>
    @if ($badge)
        <div class="flex items-center justify-between gap-3">
            <span class="cb-badge border-white/50 bg-white/60 text-[10px] tracking-[0.22em] text-slate-500">{{ $badge }}</span>
            <span class="text-slate-400 transition group-hover:translate-x-0.5">&gt;</span>
        </div>
    @endif

    @if ($title)
        <h3 class="{{ $badge ? 'mt-3' : '' }} text-base font-bold text-slate-900">{{ $title }}</h3>
    @endif

    @if ($description)
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }}</p>
    @endif
</a>
