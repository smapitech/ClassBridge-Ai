@props([
    'title' => 'Nothing here yet',
    'description' => null,
    'primaryLabel' => null,
    'primaryHref' => null,
    'secondaryLabel' => null,
    'secondaryHref' => null,
    'tone' => 'neutral',
])

@php
    $iconToneClasses = match ($tone) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-100',
        default => 'bg-slate-100 text-slate-500 ring-slate-200',
    };
@endphp

<div {{ $attributes->class('cb-empty-state') }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ring-1 {{ $iconToneClasses }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m7-6a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>

    <h3 class="mt-5 text-lg font-bold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 leading-6 text-slate-600">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-6 flex flex-wrap justify-center gap-3">{{ $actions }}</div>
    @else
        @if ($primaryLabel && $primaryHref)
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ $primaryHref }}" class="cb-btn-primary">{{ $primaryLabel }}</a>
                @if ($secondaryLabel && $secondaryHref)
                    <a href="{{ $secondaryHref }}" class="cb-btn-secondary">{{ $secondaryLabel }}</a>
                @endif
            </div>
        @endif
    @endisset
</div>
