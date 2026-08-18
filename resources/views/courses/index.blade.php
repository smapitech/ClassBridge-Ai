@extends('layouts.dashboard')
@section('title', 'Courses')

@php
    $statusTone = fn ($status) => match ($status) {
        'active' => 'success',
        'inactive' => 'warning',
        'archived' => 'neutral',
        default => 'neutral',
    };
@endphp

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Curriculum setup"
        title="Courses"
        description="Build the course first, then add subjects, attach learners or groups, and schedule live lessons from one place."
        badge="Main curriculum page"
        badgeTone="info"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('live-lessons.create') }}">Start a Live Lesson</x-secondary-button>
            <x-primary-button href="{{ route('courses.create') }}">Create course</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-dashboard-card title="Unassigned subjects" description="Subjects not yet attached to a course.">
            <div class="text-3xl font-black text-slate-900">{{ $unassignedSubjects }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Unassigned groups" description="Classes / groups still waiting for a course.">
            <div class="text-3xl font-black text-slate-900">{{ $unassignedClasses }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Live lessons" description="Recent sessions tied to courses.">
            <div class="text-3xl font-black text-slate-900">{{ $liveLessons->count() }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Courses" description="Your current curriculum structures.">
            <div class="text-3xl font-black text-slate-900">{{ $courses->total() }}</div>
        </x-dashboard-card>
    </section>

    <x-dashboard-card title="Course list" description="View the curriculum structure at a glance.">
        @if ($courses->isNotEmpty())
            <div class="overflow-hidden rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Course</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Subjects</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Groups</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Learners</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Live sessions</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Next session</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($courses as $course)
                                @php
                                    $nextSession = $course->liveClassrooms->firstWhere('status', 'live')
                                        ?? $course->liveClassrooms->firstWhere('status', 'scheduled')
                                        ?? $course->liveClassrooms->first();
                                @endphp
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-5 py-4 align-top">
                                        <div class="font-semibold text-slate-900">{{ $course->name }}</div>
                                        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($course->description ?: 'No description yet.', 90) }}</p>
                                    </td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $course->subjects_count }}</td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $course->classes_count }}</td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $course->learners_count }}</td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">{{ $course->live_classrooms_count }}</td>
                                    <td class="px-5 py-4 align-top text-sm text-slate-700">
                                        @if ($nextSession)
                                            <div class="font-medium text-slate-900">{{ $nextSession->title }}</div>
                                            <div class="text-xs text-slate-500">
                                                {{ optional($nextSession->starts_at ?? $nextSession->scheduled_at ?? $nextSession->created_at)->format('M j, g:i A') }}
                                            </div>
                                        @else
                                            <span class="text-slate-400">No session yet</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <x-status-badge :tone="$statusTone($course->status)">{{ ucfirst($course->status) }}</x-status-badge>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('courses.show', $course) }}" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>
                                            <a href="{{ route('courses.edit', $course) }}" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                            <a href="{{ route('courses.show', $course) }}#add-subject" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Add subject</a>
                                            <a href="{{ route('courses.show', $course) }}#assign-audience" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Assign learners</a>
                                            <a href="{{ route('live-lessons.create', ['course_id' => $course->id]) }}" class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Schedule lesson</a>
                                            @if ($course->status !== 'archived')
                                                <form method="POST" action="{{ route('courses.archive', $course) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-full border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                                                        Archive
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <x-empty-state
                title="No courses yet"
                description="Create the first course so subjects, learners, and live lessons have one curriculum home."
                primaryLabel="Create course"
                primaryHref="{{ route('courses.create') }}"
                secondaryLabel="Start a Live Lesson"
                secondaryHref="{{ route('live-lessons.create') }}"
            />
        @endif

        <div class="mt-6">
            {{ $courses->links() }}
        </div>
    </x-dashboard-card>

    @if ($liveLessons->isNotEmpty())
        <x-dashboard-card title="Recent course-linked live lessons" description="The newest protected rooms connected to courses.">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($liveLessons as $lesson)
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $lesson->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $lesson->course?->name ?? 'No course' }}</div>
                            </div>
                            <x-status-badge tone="{{ $lesson->status === 'live' ? 'success' : 'info' }}">{{ ucfirst($lesson->status) }}</x-status-badge>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            {{ optional($lesson->starts_at ?? $lesson->scheduled_at ?? $lesson->created_at)->format('M j, g:i A') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </x-dashboard-card>
    @endif
</div>
@endsection
