@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'badge' => null,
    'badgeTone' => 'neutral',
    'compact' => false,
])

@php
    $badgeToneClasses = match ($badgeTone) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
@endphp

<section {{ $attributes->class([$compact ? 'cb-card px-5 py-5 sm:px-6' : 'cb-surface px-6 py-8 sm:px-8']) }}>
    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            @if ($eyebrow)
                <p class="cb-page-kicker">{{ $eyebrow }}</p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-3">
                @if ($title)
                    <h1 class="{{ $compact ? 'text-2xl font-black tracking-tight text-slate-900 sm:text-3xl' : 'cb-page-title' }}">{{ $title }}</h1>
                @endif

                @if ($badge)
                    <span class="cb-badge {{ $badgeToneClasses }}">{{ $badge }}</span>
                @endif
            </div>

            @if ($description)
                <p class="mt-3 max-w-2xl {{ $compact ? 'text-sm leading-6 text-slate-600' : 'cb-page-copy' }}">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap gap-3">{{ $actions }}</div>
        @endisset
    </div>
</section>
