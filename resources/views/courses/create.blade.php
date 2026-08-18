@extends('layouts.dashboard')
@section('title', 'Create Course')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Curriculum setup"
        title="Create course"
        description="Start with the course, then attach subjects, groups, learners, and live lessons."
        badge="Course setup"
        badgeTone="info"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('courses.index') }}">Back to courses</x-secondary-button>
            <x-primary-button href="{{ route('live-lessons.create') }}">Start a Live Lesson</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="cb-surface p-6 sm:p-8">
            @include('courses.partials.form', [
                'action' => route('courses.store'),
                'submitLabel' => 'Create Course',
            ])
        </div>

        <aside class="space-y-4">
            <div class="cb-surface p-6">
                <p class="cb-page-kicker">Examples</p>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                    <li><span class="font-semibold text-slate-900">Primary 4 English</span> for a school class.</li>
                    <li><span class="font-semibold text-slate-900">Fatima&apos;s English Lessons</span> for a private tutor.</li>
                    <li><span class="font-semibold text-slate-900">Coding Starter</span> for a small academy.</li>
                </ul>
            </div>

            <div class="cb-surface p-6">
                <p class="cb-page-kicker">Next step</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    After saving, add subjects, attach groups or learners, and create the first live lesson.
                </p>
            </div>
        </aside>
    </div>
</div>
@endsection
