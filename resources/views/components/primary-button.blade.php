@props(['href' => null, 'type' => 'button', 'variant' => 'default'])

@php
    $variantClasses = match ($variant) {
        'light' => '!bg-white !text-slate-950 !shadow-none hover:!bg-slate-100',
        default => 'cb-btn-primary',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($variantClasses) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($variantClasses) }}>{{ $slot }}</button>
@endif
