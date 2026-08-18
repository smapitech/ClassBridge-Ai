@extends('layouts.dashboard')
@section('title', 'Getting Started')

@section('content')
<div class="space-y-8">
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-slate-950 px-6 py-8 text-white shadow-2xl shadow-slate-950/20 sm:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">Onboarding</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                    {{ $school->isPrivateTutorWorkspace() ? 'Simplified tutor onboarding' : 'Organization onboarding' }}
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-300">
                    {{ $school->isPrivateTutorWorkspace()
                        ? 'Set up your tutor profile, add a student, optionally link a parent, and open the protected classroom without building a full school structure first.'
                        : 'Complete the organization profile, add teachers, classes, and learners, then launch live teaching inside the protected classroom.' }}
                </p>
            </div>

            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">Progress</p>
                <p class="mt-2 text-3xl font-black">{{ $completed }} / {{ $total }}</p>
                <p class="mt-1 text-sm text-slate-300">steps completed</p>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Setup steps</h2>
                    <p class="text-sm text-slate-500">These are the actions that turn a blank workspace into a live teaching room.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $school->organizationTypeLabel() }}</span>
            </div>

            <div class="mt-6 space-y-4">
                @foreach ($blueprint as $step)
                    @php $record = $steps->get($step['step_key']); @endphp
                    <article class="rounded-[1.5rem] border px-5 py-4 {{ $record?->completed_at ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-100 bg-slate-50' }}">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div class="max-w-2xl">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-base font-bold text-slate-900">{{ $step['title'] }}</h3>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $record?->completed_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $record?->completed_at ? 'Completed' : 'Pending' }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step['description'] }}</p>
                            </div>

                            @if (!empty($step['cta_route']))
                                <x-primary-button href="{{ route($step['cta_route']) }}" class="shrink-0">
                                    {{ $step['cta_label'] ?? 'Open' }}
                                </x-primary-button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Protected workspace notice</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    ClassBridge AI is a protected learning workspace. Teachers and students interact only inside this classroom. Teachers cannot access the student&apos;s computer, files, desktop, browser history, or other applications.
                </p>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Current workspace</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Organization</span>
                        <span class="font-semibold text-slate-900">{{ $school->displayLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Type</span>
                        <span class="font-semibold text-slate-900">{{ $school->organizationTypeLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Teaching mode</span>
                        <span class="font-semibold text-slate-900">{{ $school->preferredTeachingModeLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Timezone</span>
                        <span class="font-semibold text-slate-900">{{ $school->timezone }}</span>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-secondary-button href="{{ route('organization.profile') }}">
                        Open profile
                    </x-secondary-button>
                    <x-primary-button href="{{ route('live-lessons.create') }}">
                        Start a Live Lesson
                    </x-primary-button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
