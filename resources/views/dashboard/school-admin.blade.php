@extends('layouts.dashboard')
@section('title', 'Organization Dashboard')

@section('content')
@php
    $isTutorWorkspace = $school?->isPrivateTutorWorkspace() ?? false;
    $recentLearners = $recentLearners ?? collect();
    $pendingAssignments = $pendingAssignments ?? collect();
    $workAwaitingReview = $workAwaitingReview ?? collect();
    $upcomingSessions = $upcomingSessions ?? collect();
    $recentSessions = $recentSessions ?? collect();
    $nextSession = $nextSession ?? null;
@endphp

<div class="space-y-8">
    @if ($school)
        <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[linear-gradient(135deg,_#ffffff_0%,_#f8fafc_55%,_#eff6ff_100%)] px-6 py-8 shadow-2xl shadow-slate-950/10 sm:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Organization workspace</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">{{ $school->displayLabel() }}</h1>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ $school->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($school->status === 'trial' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst($school->status) }}
                        </span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $school->organizationTypeLabel() }}
                        </span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $school->preferredTeachingModeLabel() }}
                        </span>
                    </div>
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600">
                        {{ $isTutorWorkspace
                            ? 'Keep one learner, one lesson, and one protected classroom in the same workspace.'
                            : 'Keep lessons, learners, parents, and review work in one teaching hub.' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-primary-button href="{{ route('live-lessons.create') }}">
                        Start Live Lesson
                    </x-primary-button>
                    <x-secondary-button href="{{ route('classrooms.index') }}">
                        Live Sessions
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('courses.index') }}">
                        Courses
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('school.students.create') }}">
                        Add learner
                    </x-secondary-button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">Teaching</p>
                <p class="mt-3 text-2xl font-black tracking-tight">Start Live Lesson</p>
                <p class="mt-2 text-sm leading-6 text-white/70">Open the protected classroom and begin teaching.</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Next scheduled lesson</p>
                @if ($nextSession)
                    <p class="mt-3 text-xl font-black text-slate-900">{{ $nextSession->title }}</p>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $nextSession->subject?->name ?? 'General session' }}
                        @if ($nextSession->classe?->name)
                            <span class="mx-1">&middot;</span>{{ $nextSession->classe->name }}
                        @endif
                    </p>
                    <p class="mt-3 text-sm text-slate-500">
                        {{ optional($nextSession->scheduled_at ?? $nextSession->created_at)->format('M j, g:i A') }}
                    </p>
                    <div class="mt-4">
                        <x-secondary-button href="{{ route('classrooms.show', $nextSession) }}">
                            Open lesson
                        </x-secondary-button>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-500">No lesson scheduled yet.</p>
                    <div class="mt-4">
                        <x-secondary-button href="{{ route('live-lessons.create') }}">
                            Schedule a lesson
                        </x-secondary-button>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Active learners</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['students_learners'] }}</p>
                @if ($recentLearners->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($recentLearners->take(3) as $learner)
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-[11px] font-bold text-emerald-700">
                                    {{ strtoupper(substr($learner->displayName(), 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $learner->displayName() }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $learner->email }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">No learners added yet.</p>
                    <div class="mt-4">
                        <x-secondary-button href="{{ route('school.students.create') }}">
                            Add learner
                        </x-secondary-button>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Work awaiting review</p>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $workAwaitingReview->count() }}</p>
                @if ($workAwaitingReview->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($workAwaitingReview->take(2) as $item)
                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                <p class="text-sm font-semibold text-slate-900">{{ $item['type'] }}: {{ $item['title'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item['student'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">No work awaiting review.</p>
                    <div class="mt-4">
                        <x-secondary-button href="{{ route('academic.homeworks.index') }}">
                            Open assignments
                        </x-secondary-button>
                    </div>
                @endif
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Upcoming sessions</h2>
                        <p class="text-sm text-slate-500">The next lessons waiting in your workspace.</p>
                    </div>
                    <a href="{{ route('classrooms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View all</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($upcomingSessions as $session)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $session->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $session->subject?->name ?? 'General session' }}
                                        @if ($session->classe?->name)
                                            <span class="mx-1">&middot;</span>{{ $session->classe->name }}
                                        @endif
                                    </div>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-slate-700">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </div>
                            <div class="mt-3 text-xs text-slate-500">
                                {{ optional($session->scheduled_at ?? $session->created_at)->format('M j, g:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                            No lesson scheduled yet.
                            <div class="mt-4">
                                <x-secondary-button href="{{ route('live-lessons.create') }}">
                                    Schedule a lesson
                                </x-secondary-button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Recent learners</h2>
                        <p class="text-sm text-slate-500">Keep an eye on the people you teach most often.</p>
                    </div>
                    <a href="{{ route('school.students.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open learners</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($recentLearners as $learner)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 px-4 py-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-xs font-bold text-emerald-700">
                                {{ strtoupper(substr($learner->displayName(), 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $learner->displayName() }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $learner->email }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                            No learners added yet.
                            <div class="mt-4">
                                <x-secondary-button href="{{ route('school.students.create') }}">
                                    Add learner
                                </x-secondary-button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Pending assignments</h2>
                        <p class="text-sm text-slate-500">Work that still needs to be sent or planned.</p>
                    </div>
                    <a href="{{ route('academic.homeworks.create') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Create assignment</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($pendingAssignments as $assignment)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                                            {{ $assignment['type'] }}
                                        </span>
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $assignment['title'] }}</p>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $assignment['class'] }}@if ($assignment['subject']) <span class="mx-1">&middot;</span>{{ $assignment['subject'] }}@endif</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $assignment['status'] === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($assignment['status']) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                            No assignments yet.
                            <div class="mt-4">
                                <x-secondary-button href="{{ route('academic.homeworks.create') }}">
                                    Create assignment
                                </x-secondary-button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Recent teaching activity</h2>
                        <p class="text-sm text-slate-500">Recent sessions, joined rooms, and lesson movement.</p>
                    </div>
                    <a href="{{ route('classrooms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open hub</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($recentSessions as $session)
                        <div class="rounded-2xl border border-slate-100 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $session->title }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $session->teacher?->displayName() ?? 'Teacher' }}
                                        @if ($session->classe?->name)
                                            <span class="mx-1">&middot;</span>{{ $session->classe->name }}
                                        @endif
                                    </div>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-slate-500">
                                {{ optional($session->scheduled_at ?? $session->created_at)->format('M j, g:i A') }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                            No teaching activity yet.
                            <div class="mt-4">
                                <x-secondary-button href="{{ route('live-lessons.create') }}">
                                    Start Live Lesson
                                </x-secondary-button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @else
        <section class="rounded-[2rem] border border-white/70 bg-white/95 px-6 py-10 shadow-2xl shadow-slate-950/10 sm:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Organization workspace</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">No organization is linked to this account yet.</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Finish your organization profile so the dashboard can show live sessions, learners, parents, and classroom tools in one place.
                </p>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-primary-button href="{{ route('organization.profile') }}">
                    Complete organization profile
                </x-primary-button>
                <x-secondary-button href="{{ route('organization.onboarding') }}">
                    Open getting started
                </x-secondary-button>
            </div>
        </section>
    @endif
</div>
@endsection
