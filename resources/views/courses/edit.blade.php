@extends('layouts.dashboard')
@section('title', 'Edit Course')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Curriculum setup"
        title="Edit course"
        description="Refine the course name, description, or status without changing the rest of the curriculum."
        badge="Course"
        badgeTone="info"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('courses.show', $course) }}">Back to course</x-secondary-button>
            <x-primary-button href="{{ route('live-lessons.create', ['course_id' => $course->id]) }}">Schedule lesson</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="cb-surface p-6 sm:p-8">
        @include('courses.partials.form', [
            'course' => $course,
            'action' => route('courses.update', $course),
            'method' => 'PUT',
            'submitLabel' => 'Save changes',
        ])
    </div>
</div>
@endsection
