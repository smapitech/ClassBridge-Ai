@extends('layouts.dashboard')
@section('title', 'Parent Portal')

@php
    $attendanceRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : 0;
@endphp

@section('content')
<div class="space-y-8">
    <section class="rounded-[2rem] border border-white/70 bg-white/95 px-6 py-8 shadow-2xl shadow-slate-950/10 sm:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Parent portal</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">{{ $student->displayName() }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    Track your child's progress, see teacher feedback, and review reports without leaving the ClassBridge AI workspace.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('academic.attendance.parent') }}">
                    Attendance
                </x-primary-button>
                <x-secondary-button href="{{ route('academic.reports.index') }}">
                    Reports
                </x-secondary-button>
            </div>
        </div>
    </section>

    @if ($allChildren->count() > 1)
        <section class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Linked children</h2>
                    <p class="text-sm text-slate-500">Switch between child profiles.</p>
                </div>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $allChildren->count() }} profiles</span>
            </div>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($allChildren as $child)
                    <a href="{{ route('parent-portal.dashboard', ['child_id' => $child->id]) }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $child->id === $student->id ? 'bg-slate-950 text-white' : 'border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:text-slate-900' }}">
                        {{ $child->displayName() }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Attendance</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $attendanceRate }}%</p>
            <p class="mt-1 text-xs text-slate-500">{{ $attPresent }} of {{ $attTotal }} sessions present</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Homework</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $homeworkDone }}</p>
            <p class="mt-1 text-xs text-slate-500">Reviewed submissions</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Quiz average</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ round((float) $avgScore) }}%</p>
            <p class="mt-1 text-xs text-slate-500">Recent attempts</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Points</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $totalPoints }}</p>
            <p class="mt-1 text-xs text-slate-500">Gamification score</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Progress</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $progressPercent }}%</p>
            <p class="mt-1 text-xs text-slate-500">Toward the next level</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Teacher feedback</h2>
                    <p class="text-sm text-slate-500">Recent feedback shared with parents.</p>
                </div>
                <a href="{{ route('academic.feedback.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open all</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($feedback as $item)
                    <div class="rounded-2xl border border-slate-100 px-4 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $item->teacher?->displayName() ?? 'Teacher' }}</p>
                            <p class="text-xs text-slate-500">{{ $item->created_at->format('M j, Y') }}</p>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item->feedback }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No parent-visible feedback has been posted yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Child progress</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Current class</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $upcomingClass?->name ?? 'No class linked' }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $upcomingClass?->teacher?->displayName() ?? 'Teacher not assigned' }}</p>
                </div>

                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Badges</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($badges as $badge)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 shadow-sm">{{ $badge->badge?->name ?? 'Badge' }}</span>
                        @empty
                            <span class="text-sm text-slate-500">No badges yet.</span>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl bg-sky-50 px-4 py-4 text-sky-800">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em]">Next level</p>
                    <p class="mt-2 text-sm font-semibold">{{ $nextLevel?->name ?? 'Keep learning' }}</p>
                    <p class="mt-1 text-xs">{{ $progressPercent }}% toward the next milestone.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Published reports</h2>
                    <p class="text-sm text-slate-500">Downloadable summaries for this child.</p>
                </div>
                <a href="{{ route('academic.reports.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">All reports</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($reports as $report)
                    <a href="{{ route('parent-portal.report.view', $report) }}" class="block rounded-2xl border border-slate-100 px-4 py-4 transition hover:border-slate-200 hover:bg-slate-50">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-semibold text-slate-900">{{ $report->report_type }}</p>
                            <p class="text-xs text-slate-500">{{ $report->created_at->format('M j, Y') }}</p>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">{{ $report->status === 'published' ? 'Published' : ucfirst($report->status) }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No published reports yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Upcoming class</h2>
                    <p class="text-sm text-slate-500">Next scheduled learning time.</p>
                </div>
                <a href="{{ route('lesson-replays.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Lesson replays</a>
            </div>

            <div class="mt-5 rounded-3xl bg-slate-50 px-4 py-4">
                <p class="text-sm font-semibold text-slate-900">{{ $upcomingClass?->name ?? 'No class scheduled' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $upcomingClass?->teacher?->displayName() ?? 'Teacher not assigned' }}</p>
                <p class="mt-3 text-xs text-slate-500">{{ $upcomingClass?->scheduled_at?->format('M j, Y g:i A') ?? 'Schedule not set' }}</p>
            </div>

            <div class="mt-5 rounded-3xl bg-emerald-50 px-4 py-4 text-emerald-700">
                <p class="text-sm font-semibold">Parent summary</p>
                <p class="mt-2 text-sm leading-6 text-emerald-800">
                    Stay close to your child's learning with live teaching updates, reviewed work, and parent-visible feedback.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
