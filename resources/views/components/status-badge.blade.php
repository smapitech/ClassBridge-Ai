@props(['tone' => 'neutral'])

@php
    $toneClasses = match ($tone) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
        'purple' => 'bg-violet-50 text-violet-700 border-violet-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
@endphp

<span {{ $attributes->class(['cb-badge', $toneClasses]) }}>{{ $slot }}</span>
