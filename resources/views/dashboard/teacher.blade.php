@extends('layouts.dashboard')
@section('title', 'Teacher Dashboard')

@section('content')
@php
    $myStudents = $myStudents ?? collect();
    $pendingAssignments = $pendingAssignments ?? collect();
    $recentSessions = $recentSessions ?? collect();
    $upcomingSessions = $upcomingSessions ?? collect();
    $nextSession = $nextSession ?? null;
    $reviewCount = (int) (($pendingHomeworkReviews ?? 0) + ($pendingCodingReviews ?? 0));
@endphp

<div class="space-y-8">
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(34,197,94,0.10),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.10),_transparent_24%),linear-gradient(135deg,_#ffffff_0%,_#f8fafc_55%,_#eff6ff_100%)] px-6 py-8 shadow-2xl shadow-slate-950/10 sm:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Teaching workspace</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Welcome back, {{ $user->displayName() }}.</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    Start one live lesson, switch modes inside the same room, and keep teaching focused on the learner.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('live-lessons.create') }}">
                    Start Live Lesson
                </x-primary-button>
                <x-secondary-button href="{{ route('courses.index') }}">
                    Courses
                </x-secondary-button>
                <x-secondary-button href="{{ route('library.index') }}">
                    Teaching Library
                </x-secondary-button>
                <x-secondary-button href="{{ route('ai.teacher.index') }}">
                    AI Lesson Builder
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
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['my_students'] }}</p>
            @if ($myStudents->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($myStudents->take(3) as $student)
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-[11px] font-bold text-emerald-700">
                                {{ strtoupper(substr($student->displayName(), 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $student->displayName() }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $student->email }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">No learners assigned yet.</p>
                <div class="mt-4">
                    <x-secondary-button href="{{ route('school.students.create') }}">
                        Add learner
                    </x-secondary-button>
                </div>
            @endif
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Work awaiting review</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $reviewCount }}</p>
            @if ($pendingAssignments->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($pendingAssignments->take(2) as $assignment)
                        <div class="rounded-2xl bg-slate-50 px-3 py-2">
                            <p class="text-sm font-semibold text-slate-900">{{ $assignment['type'] }}: {{ $assignment['title'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $assignment['class'] }}@if ($assignment['subject']) <span class="mx-1">&middot;</span>{{ $assignment['subject'] }}@endif</p>
                        </div>
                    @endforeach
                </div>
            @elseif ($reviewCount > 0)
                <p class="mt-4 text-sm text-slate-500">You have submissions waiting in the review queue.</p>
                <div class="mt-4">
                    <x-secondary-button href="{{ route('academic.homeworks.index') }}">
                        Open assignments
                    </x-secondary-button>
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
                    <p class="text-sm text-slate-500">Jump into the room when teaching time begins.</p>
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
                    <p class="text-sm text-slate-500">The learners you interact with most often.</p>
                </div>
                <a href="{{ route('school.students.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open learners</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($myStudents as $student)
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-xs font-bold text-emerald-700">
                            {{ strtoupper(substr($student->displayName(), 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $student->displayName() }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $student->email }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                        No learners assigned yet.
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
                    <p class="text-sm text-slate-500">Recent sessions and room movement.</p>
                </div>
                <a href="{{ route('classrooms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open hub</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentSessions as $session)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $session->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $session->classe?->name ?? 'General classroom' }}</div>
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
</div>
@endsection
