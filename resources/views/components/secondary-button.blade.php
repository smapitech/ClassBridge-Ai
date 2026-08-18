@props(['href' => null, 'type' => 'button', 'variant' => 'default'])

@php
    $variantClasses = match ($variant) {
        'danger' => '!border-rose-200 !bg-rose-50 !text-rose-700 hover:!bg-rose-100',
        'inverse' => '!border-white/20 !bg-transparent !text-white hover:!bg-white/10 hover:!text-white',
        default => 'cb-btn-secondary',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($variantClasses) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($variantClasses) }}>{{ $slot }}</button>
@endif
