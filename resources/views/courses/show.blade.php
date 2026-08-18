@extends('layouts.dashboard')
@section('title', $course->name)

@php
    $isPrivateTutorWorkspace = (bool) ($course->school?->isPrivateTutorWorkspace() ?? false);
    $statusTone = match ($course->status) {
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
        title="{{ $course->name }}"
        description="{{ $course->description ?: 'Course overview, subjects, learners or groups, sessions, assignments, and teaching materials all in one place.' }}"
        badge="{{ ucfirst($course->status) }}"
        badgeTone="{{ $statusTone }}"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('courses.index') }}">Back to courses</x-secondary-button>
            <x-secondary-button href="{{ route('courses.edit', $course) }}">Edit course</x-secondary-button>
            <x-primary-button href="{{ route('live-lessons.create', ['course_id' => $course->id]) }}">Schedule lesson</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-dashboard-card title="Subjects" description="Subjects inside this course.">
            <div class="text-3xl font-black text-slate-900">{{ $course->subjects->count() }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="{{ $isPrivateTutorWorkspace ? 'Learners' : 'Groups' }}" description="Assigned learners or classes.">
            <div class="text-3xl font-black text-slate-900">{{ $course->classes->count() + $course->learners->count() }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Live sessions" description="Protected rooms linked to the course.">
            <div class="text-3xl font-black text-slate-900">{{ $course->liveClassrooms->count() }}</div>
        </x-dashboard-card>
        <x-dashboard-card title="Materials" description="Tagged teaching materials.">
            <div class="text-3xl font-black text-slate-900">{{ $materials->count() }}</div>
        </x-dashboard-card>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <x-dashboard-card title="Course overview" description="One course can hold several subjects and live lessons.">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Status</p>
                        <x-status-badge class="mt-3" :tone="$statusTone">{{ ucfirst($course->status) }}</x-status-badge>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Next live session</p>
                        @php $nextSession = $upcomingSessions->first(); @endphp
                        @if ($nextSession)
                            <p class="mt-3 text-sm font-semibold text-slate-900">{{ $nextSession->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ optional($nextSession->starts_at ?? $nextSession->scheduled_at ?? $nextSession->created_at)->format('M j, g:i A') }}</p>
                        @else
                            <p class="mt-3 text-sm text-slate-500">No upcoming session yet.</p>
                        @endif
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Created</p>
                        <p class="mt-3 text-sm font-semibold text-slate-900">{{ optional($course->created_at)->format('M j, Y') }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('courses.edit', $course) }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Edit course</a>
                    <a href="{{ route('live-lessons.create', ['course_id' => $course->id]) }}" class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Schedule lesson</a>
                    <a href="#add-subject" class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Add subject</a>
                    <a href="#assign-audience" class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Assign learners</a>
                </div>
            </x-dashboard-card>

            <x-dashboard-card id="subjects" title="Subjects" description="Subjects created inside this course.">
                <div class="space-y-4">
                    @forelse ($course->subjects as $subject)
                        <div class="flex items-start justify-between gap-3 rounded-3xl border border-slate-200 bg-white p-4">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $subject->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $subject->description ?: 'No subject note yet.' }}</div>
                            </div>
                            <x-status-badge tone="{{ $subject->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($subject->status) }}</x-status-badge>
                        </div>
                    @empty
                        <x-empty-state
                            title="No subjects yet"
                            description="Create the first subject to keep this course organized."
                            tone="info"
                        />
                    @endforelse
                </div>
            </x-dashboard-card>

            <x-dashboard-card id="add-subject" title="Add subject" description="Create a subject under this course.">
                <form method="POST" action="{{ route('courses.subjects.store', $course) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Subject name</label>
                        <input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Grammar">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Category</label>
                        <input name="category" value="{{ old('category') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Languages">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Short note for the teacher.">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-primary-button type="submit" class="w-full justify-center">Add subject</x-primary-button>
                    </div>
                </form>
            </x-dashboard-card>

            <x-dashboard-card title="{{ $isPrivateTutorWorkspace ? 'Learners' : 'Learners and groups' }}" description="Connect people to the course.">
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">Groups / classes</h3>
                            <span class="text-xs text-slate-500">{{ $course->classes->count() }}</span>
                        </div>

                        @if ($course->classes->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($course->classes as $class)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div class="text-sm font-semibold text-slate-900">{{ $class->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $class->level ?? $class->age_group ?? 'Group' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                No classes assigned yet.
                            </p>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">Individual learners</h3>
                            <span class="text-xs text-slate-500">{{ $course->learners->count() }}</span>
                        </div>

                        @if ($course->learners->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($course->learners as $learner)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <div class="text-sm font-semibold text-slate-900">{{ $learner->displayName() }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $learner->email }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                No learners assigned yet.
                            </p>
                        @endif
                    </div>
                </div>

                <div id="assign-audience" class="mt-6 rounded-[1.5rem] border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Assign to this course</h3>
                            <p class="mt-1 text-xs text-slate-500">Add classes/groups and learners without leaving the page.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('courses.assign', $course) }}" class="mt-5 grid gap-4 lg:grid-cols-2">
                        @csrf

                        @if (! $isPrivateTutorWorkspace)
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Classes / groups</label>
                                <select name="class_ids[]" multiple size="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                                    @foreach (App\Models\Classe::forSchool($course->school_id)->orderBy('name')->get(['id', 'name']) as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Learners</label>
                            <select name="learner_ids[]" multiple size="5" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                                @foreach (App\Models\User::where('school_id', $course->school_id)->whereHas('role', fn ($query) => $query->where('slug', 'student'))->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']) as $learner)
                                    <option value="{{ $learner->id }}">{{ trim($learner->first_name . ' ' . $learner->last_name) ?: $learner->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2 flex flex-wrap gap-3">
                            <x-primary-button type="submit">Save audience</x-primary-button>
                            <x-secondary-button href="{{ route('courses.index') }}">Back to courses</x-secondary-button>
                        </div>
                    </form>
                </div>
            </x-dashboard-card>
        </div>

        <div class="space-y-6">
            <x-dashboard-card title="Upcoming sessions" description="Live lessons attached to this course.">
                <div class="space-y-3">
                    @forelse ($upcomingSessions as $session)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
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
                                <x-status-badge tone="{{ $session->status === 'live' ? 'success' : 'info' }}">{{ ucfirst($session->status) }}</x-status-badge>
                            </div>
                            <div class="mt-3 text-xs text-slate-500">
                                {{ optional($session->starts_at ?? $session->scheduled_at ?? $session->created_at)->format('M j, g:i A') }}
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            title="No upcoming sessions"
                            description="Schedule the first live lesson for this course."
                            primaryLabel="Schedule lesson"
                            primaryHref="{{ route('live-lessons.create', ['course_id' => $course->id]) }}"
                            tone="info"
                        />
                    @endforelse
                </div>
            </x-dashboard-card>

            <x-dashboard-card title="Previous sessions" description="Finished live lessons for this course.">
                <div class="space-y-3">
                    @forelse ($previousSessions as $session)
                        <div class="rounded-3xl border border-slate-200 bg-white p-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $session->title }}</div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $session->teacher?->displayName() ?? 'Teacher' }}
                                <span class="mx-1">&middot;</span>
                                {{ optional($session->starts_at ?? $session->scheduled_at ?? $session->created_at)->format('M j, g:i A') }}
                            </div>
                        </div>
                    @empty
                        <p class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                            No previous sessions yet.
                        </p>
                    @endforelse
                </div>
            </x-dashboard-card>

            <x-dashboard-card title="Assignments" description="Homework, quizzes, and worksheets linked to this course.">
                <div class="space-y-3">
                    @forelse ($assignments as $assignment)
                        <div class="rounded-3xl border border-slate-200 bg-white p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $assignment['type'] }}</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ $assignment['title'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ optional($assignment['date'])->format('M j, g:i A') }}</div>
                                </div>
                                <x-status-badge tone="{{ $assignment['status'] === 'published' || $assignment['status'] === 'active' ? 'success' : 'warning' }}">{{ ucfirst($assignment['status']) }}</x-status-badge>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            title="No assignments yet"
                            description="Create homework or quizzes after you plan the course."
                            primaryLabel="Create homework"
                            primaryHref="{{ route('academic.homeworks.create') }}"
                            secondaryLabel="Create quiz"
                            secondaryHref="{{ route('academic.quizzes.create') }}"
                            tone="warning"
                        />
                    @endforelse
                </div>
            </x-dashboard-card>

            <x-dashboard-card title="Teaching materials" description="Course resources from the workspace library.">
                <div class="space-y-3">
                    @forelse ($materials as $material)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $material->title }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ strtoupper($material->material_type) }} · {{ ucfirst($material->status) }}</div>
                        </div>
                    @empty
                        <x-empty-state
                            title="No materials yet"
                            description="Open the teaching library to add notes, worksheets, and resources."
                            primaryLabel="Open teaching library"
                            primaryHref="{{ route('library.index', ['course_id' => $course->id]) }}"
                            tone="info"
                        />
                    @endforelse
                </div>
            </x-dashboard-card>
        </div>
    </section>
</div>
@endsection
