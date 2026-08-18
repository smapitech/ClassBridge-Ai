@extends('layouts.dashboard')
@section('title', 'Create Classroom')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Live Interactive Classroom setup"
        title="Create a protected classroom workspace."
        description="Build a live room for schools, tutoring businesses, homeschool tutors, lesson teachers, and remote teachers. The room keeps the interaction inside ClassBridge AI only."
        badge="Protected"
        badgeTone="info"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('classrooms.index') }}">
                Open classroom hub
            </x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <form method="POST" action="{{ route('classrooms.store') }}" class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Classroom title</label>
                    <input name="title" value="{{ old('title') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Grade 4 English live lesson">
                    @error('title')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Description</label>
                    <textarea name="description" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200" placeholder="Explain what the session will cover.">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Class</label>
                    <select name="class_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                        <option value="">No class selected</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Subject</label>
                    <select name="subject_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                        <option value="">No subject selected</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Schedule</label>
                    <input name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Session state</label>
                    <select name="status" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                        <option value="draft" selected>Draft</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <x-primary-button type="submit">
                    Create classroom
                </x-primary-button>
                <x-secondary-button href="{{ route('classrooms.index') }}">
                    Cancel
                </x-secondary-button>
            </div>
        </form>

        <aside class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Why this matters</p>
            <div class="mt-5 space-y-4 text-sm leading-6 text-slate-600">
                <p>Use this room for private tutoring, homeschool support, after-school lessons, or school classes.</p>
                <p>The live room shares a whiteboard, shared code editor, and shared text pad without exposing the learner's private computer.</p>
                <p>Students join by room code, and teachers stay fully inside the ClassBridge AI workspace.</p>
            </div>
        </aside>
    </section>
</div>
@endsection
