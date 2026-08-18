@extends('layouts.dashboard')
@section('title', 'Edit Subject')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Curriculum</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Edit Subject</h1>
            <p class="mt-2 text-sm text-slate-500">Move the subject to another course if needed.</p>
        </div>
        <a href="{{ route('courses.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Courses</a>
    </div>

    <form method="POST" action="{{ route('school.subjects.update', $subject) }}" class="space-y-6 rounded-xl bg-white p-8 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input name="name" value="{{ old('name', $subject->name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Course</label>
                <select name="course_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">- Optional -</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id', $subject->course_id) == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">The subject will stay attached to its selected course.</p>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('description', $subject->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <input name="category" value="{{ old('category', $subject->category) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status *</label>
                <select name="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="active" {{ $subject->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $subject->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update</button>
            <a href="{{ route('courses.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back to courses</a>
        </div>
    </form>
</div>
@endsection
