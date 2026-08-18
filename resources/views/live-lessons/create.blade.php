@extends('layouts.dashboard')
@section('title', 'Start a Live Lesson')

@php
    $courseOptions = $courses ?? collect();
    $subjectOptions = $subjects ?? collect();
    $classOptions = $classes ?? collect();
    $studentOptions = $students ?? collect();
    $isPrivateTutorWorkspace = (bool) ($school?->isPrivateTutorWorkspace() ?? false);
    $defaultMode = $defaultMode ?? 'whiteboard';
    $defaultAudienceMode = $defaultAudienceMode ?? ($isPrivateTutorWorkspace ? 'learner' : 'group');

    $modeCards = [
        ['value' => 'whiteboard', 'glyph' => 'WB', 'title' => 'Whiteboard', 'description' => 'Draw, solve, and explain together.'],
        ['value' => 'coding', 'glyph' => '</>', 'title' => 'Coding Studio', 'description' => 'Write code and preview results live.'],
        ['value' => 'text', 'glyph' => 'TXT', 'title' => 'Text / English', 'description' => 'Read, write, and correct in one pad.'],
        ['value' => 'mathematics', 'glyph' => 'MATH', 'title' => 'Mathematics', 'description' => 'Work through steps, sums, and corrections.'],
        ['value' => 'presentation', 'glyph' => 'SL', 'title' => 'Presentation', 'description' => 'Walk through slides and guided notes.'],
    ];

    $permissionCards = [
        ['key' => 'allow_student_draw', 'label' => 'Allow drawing', 'help' => 'Let the learner write on the board.'],
        ['key' => 'allow_student_type', 'label' => 'Allow typing', 'help' => 'Let the learner type in the text pad.'],
        ['key' => 'allow_student_code', 'label' => 'Allow code editing', 'help' => 'Let the learner edit code with you.'],
        ['key' => 'allow_student_chat', 'label' => 'Allow chat', 'help' => 'Keep the room conversation open.'],
        ['key' => 'show_pointer', 'label' => 'Show learner pointer', 'help' => 'Share the learner pointer with the teacher.'],
        ['key' => 'allow_resource_download', 'label' => 'Allow resource download', 'help' => 'Let the learner download shared files.'],
    ];

    $startCards = [
        ['value' => 'start_now', 'title' => 'Start now', 'description' => 'Create the lesson and go live right away.'],
        ['value' => 'schedule', 'title' => 'Schedule for later', 'description' => 'Save the time and open it when ready.'],
        ['value' => 'draft', 'title' => 'Save as draft', 'description' => 'Keep it private until you are ready.'],
    ];
@endphp

@section('content')
<div
    x-data="liveLessonSetup({
        createCourseUrl: '{{ route('live-lessons.courses.store') }}',
        createSubjectUrl: '{{ route('live-lessons.subjects.store') }}',
        audienceMode: @js(old('audience_mode', $defaultAudienceMode)),
        startOption: @js(old('start_option', 'start_now')),
        initialMode: @js(old('initial_mode', $defaultMode)),
        selectedCourseId: @js(old('course_id', $prefillCourseId ?? '')),
        selectedSubjectId: @js(old('subject_id', $prefillSubjectId ?? '')),
        selectedClassId: @js(old('class_id', $prefillClassId ?? '')),
        selectedLearnerIds: @js(old('learner_ids', $prefillLearnerIds ?? [])),
        permissions: {
            allow_student_draw: @js((bool) old('permissions.allow_student_draw', true)),
            allow_student_type: @js((bool) old('permissions.allow_student_type', true)),
            allow_student_code: @js((bool) old('permissions.allow_student_code', true)),
            allow_student_chat: @js((bool) old('permissions.allow_student_chat', true)),
            show_pointer: @js((bool) old('permissions.show_pointer', true)),
            allow_resource_download: @js((bool) old('permissions.allow_resource_download', false)),
        }
    })"
    class="space-y-8"
    x-on:keydown.escape.window="courseDrawerOpen = false; subjectDrawerOpen = false"
>
    <x-page-header
        eyebrow="Unified lesson setup"
        title="Start a Live Lesson"
        description="One room code. One join link. One protected classroom for tutoring, homeschooling, coding, and school lessons."
        badge="Protected live workspace"
        badgeTone="info"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('classrooms.index') }}">
                Open lesson hub
            </x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <form method="POST" action="{{ route('live-lessons.store') }}" class="space-y-6">
            @csrf

            <section class="cb-surface p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="cb-page-kicker">Lesson details</p>
                        <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Set the room once.</h2>
                    </div>
                    <x-status-badge tone="info">Room code generated on save</x-status-badge>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Lesson title</label>
                        <input
                            name="title"
                            value="{{ old('title') }}"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                            placeholder="Year 6 fractions and live practice"
                        >
                        @error('title')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm font-semibold text-slate-700">Course</label>
                            <button type="button" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800" @click="courseDrawerOpen = true">
                                Create new course
                            </button>
                        </div>
                        <select
                            id="lesson-course-select"
                            name="course_id"
                            x-model="selectedCourseId"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                        >
                            <option value="">No course selected</option>
                            @forelse ($courseOptions as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @empty
                                <option value="" disabled>No courses yet. Create one.</option>
                            @endforelse
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Optional for one-off lessons.</p>
                        @error('course_id')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm font-semibold text-slate-700">Subject</label>
                            <button type="button" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800" @click="subjectDrawerOpen = true">
                                Create new subject
                            </button>
                        </div>
                        <select
                            id="lesson-subject-select"
                            name="subject_id"
                            x-model="selectedSubjectId"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                        >
                            <option value="">No subject selected</option>
                            @forelse ($subjectOptions as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @empty
                                <option value="" disabled>No subjects yet. Create one.</option>
                            @endforelse
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Optional, but useful for planning and reports.</p>
                        @error('subject_id')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <p class="text-sm font-semibold text-slate-700">Learner or group</p>
                        <p class="mt-1 text-xs text-slate-500">Pick one learner for private tutoring, or a class/group for schools and academies.</p>

                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition" :class="audienceMode === 'learner' ? 'border-slate-950 bg-white shadow-sm' : ''">
                                <input type="radio" name="audience_mode" value="learner" class="mt-1 text-slate-950" x-model="audienceMode">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Individual learner</span>
                                    <span class="mt-1 block text-xs text-slate-500">Best for private and homeschool tutors.</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition" :class="audienceMode === 'group' ? 'border-slate-950 bg-white shadow-sm' : ''">
                                <input type="radio" name="audience_mode" value="group" class="mt-1 text-slate-950" x-model="audienceMode">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">Class / group</span>
                                    <span class="mt-1 block text-xs text-slate-500">Best for schools and learning centers.</span>
                                </span>
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <div x-show="audienceMode === 'group'" x-cloak>
                                <label class="text-sm font-semibold text-slate-700">Class / group</label>
                                <select
                                    name="class_id"
                                    x-model="selectedClassId"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                                >
                                    <option value="">Select a class or group</option>
                                    @forelse ($classOptions as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @empty
                                        <option value="" disabled>No classes yet.</option>
                                    @endforelse
                                </select>
                                @error('class_id')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="audienceMode === 'learner'" x-cloak class="lg:col-span-2">
                                <label class="text-sm font-semibold text-slate-700">Learner(s)</label>
                                <select
                                    name="learner_ids[]"
                                    multiple
                                    size="5"
                                    x-model="selectedLearnerIds"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                                >
                                    @forelse ($studentOptions as $student)
                                        <option value="{{ $student->id }}">{{ $student->displayName() }}{{ $student->email ? ' - ' . $student->email : '' }}</option>
                                    @empty
                                        <option value="" disabled>No learners yet.</option>
                                    @endforelse
                                </select>
                                <p class="mt-2 text-xs text-slate-500">Hold Ctrl or Cmd to select more than one learner.</p>
                                @error('learner_ids')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Description</label>
                        <textarea
                            name="description"
                            rows="3"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                            placeholder="Short lesson note for the teacher and the workspace."
                        >{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Date and time</label>
                        <input
                            name="date_time"
                            type="datetime-local"
                            value="{{ old('date_time') }}"
                            :required="startOption === 'schedule'"
                            x-show="startOption === 'schedule'"
                            x-cloak
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                        >
                        <p class="mt-2 text-xs text-slate-500" x-show="startOption === 'schedule'" x-cloak>Used only when you schedule the lesson.</p>
                        @error('date_time')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-slate-700">Duration</label>
                        <input
                            name="duration_minutes"
                            type="number"
                            min="15"
                            max="480"
                            value="{{ old('duration_minutes', 60) }}"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                            placeholder="60"
                        >
                        <p class="mt-2 text-xs text-slate-500">Shorter sessions keep younger learners focused.</p>
                        @error('duration_minutes')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="cb-surface p-6 sm:p-8">
                <div>
                    <p class="cb-page-kicker">Teaching mode</p>
                    <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Pick the first mode for the lesson.</h2>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    @foreach ($modeCards as $mode)
                        <label class="group cursor-pointer rounded-3xl border p-4 transition" :class="initialMode === '{{ $mode['value'] }}' ? 'border-slate-950 bg-slate-950 text-white shadow-lg shadow-slate-950/10' : 'border-slate-200 bg-white hover:border-slate-300'">
                            <input type="radio" name="initial_mode" value="{{ $mode['value'] }}" class="sr-only" x-model="initialMode">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl text-xs font-black tracking-[0.2em] {{ $mode['value'] === $defaultMode ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $mode['glyph'] }}</div>
                                    <h3 class="mt-4 text-sm font-black">{{ $mode['title'] }}</h3>
                                    <p class="mt-1 text-xs leading-5 {{ $mode['value'] === $defaultMode ? 'text-white/70' : 'text-slate-500' }}">{{ $mode['description'] }}</p>
                                </div>
                                <span class="mt-1 inline-flex h-4 w-4 rounded-full border-2" :class="initialMode === '{{ $mode['value'] }}' ? 'border-white bg-white' : 'border-slate-300 bg-transparent'"></span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="cb-surface p-6 sm:p-8">
                <div>
                    <p class="cb-page-kicker">Learner permissions</p>
                    <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Set what the learner can use.</h2>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($permissionCards as $permission)
                        <label class="rounded-3xl border border-slate-200 bg-white p-4">
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="permissions[{{ $permission['key'] }}]" value="0">
                                <input
                                    type="checkbox"
                                    name="permissions[{{ $permission['key'] }}]"
                                    value="1"
                                    @checked(old("permissions.{$permission['key']}", $permission['key'] === 'allow_resource_download' ? false : true))
                                    class="mt-1 rounded border-slate-300 text-slate-950 focus:ring-slate-300"
                                >
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">{{ $permission['label'] }}</span>
                                    <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $permission['help'] }}</span>
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="cb-surface p-6 sm:p-8">
                <div>
                    <p class="cb-page-kicker">Start option</p>
                    <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Choose how the lesson begins.</h2>
                </div>

                <div class="mt-6 grid gap-3 md:grid-cols-3">
                    @foreach ($startCards as $startCard)
                        <label class="cursor-pointer rounded-3xl border p-4 transition" :class="startOption === '{{ $startCard['value'] }}' ? 'border-slate-950 bg-slate-950 text-white shadow-lg shadow-slate-950/10' : 'border-slate-200 bg-white hover:border-slate-300'">
                            <input type="radio" name="start_option" value="{{ $startCard['value'] }}" class="sr-only" x-model="startOption">
                            <h3 class="text-sm font-black">{{ $startCard['title'] }}</h3>
                            <p class="mt-2 text-xs leading-5 {{ $startCard['value'] === 'start_now' ? 'text-white/70' : ( $startCard['value'] === 'schedule' ? 'text-white/70' : 'text-slate-500') }}">{{ $startCard['description'] }}</p>
                        </label>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-primary-button type="submit">
                        Create Live Lesson
                    </x-primary-button>
                    <x-secondary-button href="{{ route('classrooms.index') }}">
                        Cancel
                    </x-secondary-button>
                </div>
            </section>
        </form>

        <aside class="space-y-4">
            <div class="cb-surface p-6">
                <p class="cb-page-kicker">Workspace</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $school?->displayLabel() ?? 'Your workspace' }}</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Everything stays inside one protected live room. You decide the mode, the audience, and how the session starts.</p>

                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Room code</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">Generated when you save</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Join link</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">Copied from the classroom page</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Default mode</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ \App\Enums\LiveLessonMode::label($defaultMode) }}</div>
                    </div>
                </div>
            </div>

            <div class="cb-surface p-6">
                <p class="cb-page-kicker">Safety</p>
                <h3 class="mt-2 text-lg font-black text-slate-900">Protected by design</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600">Teachers and learners interact only inside the ClassBridge room. No remote desktop access, no private files, no browser history.</p>
            </div>
        </aside>
    </section>

    <div x-cloak x-show="courseDrawerOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm" x-transition.opacity>
        <div class="cb-surface w-full max-w-lg p-6" @click.outside="courseDrawerOpen = false">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="cb-page-kicker">Quick create</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900">Create new course</h3>
                </div>
                <button type="button" class="rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-500" @click="courseDrawerOpen = false">Close</button>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Course name</label>
                    <input x-model="courseForm.name" type="text" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Reading Circle">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Description</label>
                    <textarea x-model="courseForm.description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Short course note"></textarea>
                </div>
                <p class="text-sm text-slate-500" x-text="courseStatus"></p>
                <div class="flex gap-3">
                    <x-primary-button type="button" @click="createCourse()" class="flex-1 justify-center" x-bind:disabled="busyCourse">
                        Save course
                    </x-primary-button>
                    <x-secondary-button type="button" @click="courseDrawerOpen = false">
                        Cancel
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="subjectDrawerOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm" x-transition.opacity>
        <div class="cb-surface w-full max-w-lg p-6" @click.outside="subjectDrawerOpen = false">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="cb-page-kicker">Quick create</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900">Create new subject</h3>
                </div>
                <button type="button" class="rounded-full border border-slate-200 px-3 py-1 text-sm font-semibold text-slate-500" @click="subjectDrawerOpen = false">Close</button>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Subject name</label>
                    <input x-model="subjectForm.name" type="text" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Fractions">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-700">Description</label>
                    <textarea x-model="subjectForm.description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Short subject note"></textarea>
                </div>
                <p class="text-sm text-slate-500" x-text="subjectStatus"></p>
                <div class="flex gap-3">
                    <x-primary-button type="button" @click="createSubject()" class="flex-1 justify-center" x-bind:disabled="busySubject">
                        Save subject
                    </x-primary-button>
                    <x-secondary-button type="button" @click="subjectDrawerOpen = false">
                        Cancel
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
