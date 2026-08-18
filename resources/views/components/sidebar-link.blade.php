@props(['href' => '#', 'active' => false, 'icon' => ''])

<a
    href="{{ $href }}"
    @class([
        'group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition',
        'bg-slate-950 text-white shadow-lg shadow-slate-950/10' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
>
    @if($icon)
        <svg class="h-5 w-5 flex-shrink-0 transition group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
    @endif
    {{ $slot }}
</a>
