@props(['title' => null, 'description' => null])

<div {{ $attributes->class('cb-card p-6') }}>
    @if ($title || $description || isset($actions))
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ ($title || $description || isset($actions)) ? 'mt-5' : '' }}">
        {{ $slot }}
    </div>
</div>
