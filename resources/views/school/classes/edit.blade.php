@extends('layouts.dashboard')
@section('title', 'Edit ' . $class->name)
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Class: {{ $class->name }}</h1>
    <form method="POST" action="{{ route('school.classes.update', $class) }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Name *</label><input name="name" value="{{ old('name', $class->name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('name') border-red-500 @enderror">@error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Course</label><select name="course_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="">— Optional —</option>@foreach($courses as $course)<option value="{{ $course->id }}" {{ old('course_id', $class->course_id) == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>@endforeach</select><p class="mt-1 text-xs text-gray-500">Attach the class to a course if it belongs to one.</p></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="description" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('description', $class->description) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700">Age Group</label><input name="age_group" value="{{ old('age_group', $class->age_group) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Level</label><select name="level" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="">—</option><option value="Beginner" {{ $class->level === 'Beginner' ? 'selected' : '' }}>Beginner</option><option value="Intermediate" {{ $class->level === 'Intermediate' ? 'selected' : '' }}>Intermediate</option><option value="Advanced" {{ $class->level === 'Advanced' ? 'selected' : '' }}>Advanced</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700">Status *</label><select name="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="active" {{ $class->status === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ $class->status === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
        </div>
        <div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update Class</button><a href="{{ route('school.classes.show', $class) }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
    </form>
</div>
@endsection
