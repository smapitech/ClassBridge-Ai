@props([
    'name',
    'class' => 'h-5 w-5',
])

@switch($name)
    @case('select')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 3l12 8-5 1 2 6-2 1-2-6-5 3z" />
        </svg>
        @break

    @case('hand')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M8 11V7a1.5 1.5 0 0 1 3 0v4" />
            <path d="M11 11V6a1.5 1.5 0 0 1 3 0v5" />
            <path d="M14 11V7.5a1.5 1.5 0 0 1 3 0V12" />
            <path d="M8 11c0-1.5-1-2.5-2.2-2.5A1.8 1.8 0 0 0 4 10.4v3.9a7 7 0 0 0 7 7h1.8a6 6 0 0 0 5.8-4.4l1.2-4.1" />
        </svg>
        @break

    @case('pen')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 20l4.5-1 10.5-10.5a1.7 1.7 0 0 0 0-2.4l-1.6-1.6a1.7 1.7 0 0 0-2.4 0L4.5 15 4 20Z" />
            <path d="M13.5 5.5l5 5" />
        </svg>
        @break

    @case('highlighter')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 20h6l8-8a2 2 0 0 0 0-2.8l-3.2-3.2a2 2 0 0 0-2.8 0l-8 8V20Z" />
            <path d="M14.5 6.5l3 3" />
            <path d="M3 21h8" />
        </svg>
        @break

    @case('eraser')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M7 20h10a2 2 0 0 0 1.4-3.4l-5.4-5.4a2 2 0 0 0-2.8 0L4.6 16.8A2 2 0 0 0 6 20Z" />
            <path d="M10 20l8-8" />
        </svg>
        @break

    @case('text')
        <span {{ $attributes->class('flex items-center justify-center text-lg font-black tracking-tight text-current') }} aria-hidden="true">T</span>
        @break

    @case('sticky_note')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 4h10l4 4v12H5z" />
            <path d="M15 4v4h4" />
            <path d="M8 11h6" />
            <path d="M8 15h4" />
        </svg>
        @break

    @case('shapes')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="4" width="7" height="7" rx="1.5" />
            <circle cx="17" cy="17" r="4" />
        </svg>
        @break

    @case('line')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 19L19 5" />
        </svg>
        @break

    @case('arrow')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h12" />
            <path d="M13 6l6 6-6 6" />
        </svg>
        @break

    @case('image')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="5" width="16" height="14" rx="2" />
            <path d="M8 13l2.5-2.5L14 14l2-2 4 4" />
            <circle cx="9" cy="9" r="1.2" />
        </svg>
        @break

    @case('table')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M4 10h16M4 16h16M10 4v16M16 4v16" />
        </svg>
        @break

    @case('equation')
        <span {{ $attributes->class('flex items-center justify-center text-[1.1rem] font-black leading-none text-current') }} aria-hidden="true">∑</span>
        @break

    @case('laser_pointer')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="2.5" />
            <path d="M12 4v3M20 12h-3M12 20v-3M4 12h3" />
        </svg>
        @break

    @case('comment')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H11l-5 4v-4H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
            <path d="M8 10h8M8 13h5" />
        </svg>
        @break

    @case('templates')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="4" width="6" height="6" rx="1.5" />
            <rect x="14" y="4" width="6" height="6" rx="1.5" />
            <rect x="4" y="14" width="6" height="6" rx="1.5" />
            <rect x="14" y="14" width="6" height="6" rx="1.5" />
        </svg>
        @break

    @case('more_tools')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 6h.01M12 12h.01M12 18h.01" />
        </svg>
        @break

    @case('undo')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 7H5v4" />
            <path d="M5 11c1.7-3.7 5.6-6 9.7-5.4 4.1.6 7.2 4 7.3 8.2.1 4.8-3.8 8.7-8.6 8.7-3 0-5.6-1.5-7.1-3.8" />
        </svg>
        @break

    @case('redo')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M15 7h4v4" />
            <path d="M19 11c-1.7-3.7-5.6-6-9.7-5.4-4.1.6-7.2 4-7.3 8.2-.1 4.8 3.8 8.7 8.6 8.7 3 0 5.6-1.5 7.1-3.8" />
        </svg>
        @break

    @case('fit')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 9V5h4M20 9V5h-4M4 15v4h4M20 15v4h-4" />
        </svg>
        @break

    @case('export')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3v12" />
            <path d="M8 7l4-4 4 4" />
            <path d="M5 14v5h14v-5" />
        </svg>
        @break

    @case('panel')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4" y="4" width="16" height="16" rx="3" />
            <path d="M9 4v16" />
            <path d="M15 9h5M15 15h5" />
        </svg>
        @break

    @default
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 4v16M4 12h16" />
        </svg>
@endswitch
