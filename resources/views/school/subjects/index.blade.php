@extends('layouts.dashboard')
@section('title', 'Subjects')
@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Curriculum</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Subjects</h1>
        <p class="mt-2 text-sm text-slate-500">Subjects live inside courses, not on their own.</p>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('courses.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Courses</a>
        <a href="{{ route('school.subjects.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add subject</a>
    </div>
</div>

<div class="overflow-hidden rounded-xl bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Course</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($subjects as $subject)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $subject->name }}</div>
                        <div class="text-xs text-gray-500">{{ $subject->slug }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($subject->course)
                            <a href="{{ route('courses.show', $subject->course) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                {{ $subject->course->name }}
                            </a>
                        @else
                            <span class="text-gray-400">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $subject->category ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $subject->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($subject->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('school.subjects.edit', $subject) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">Edit</a>
                        <form method="POST" action="{{ route('school.subjects.destroy', $subject) }}" class="inline" onsubmit="return confirm('Delete this subject?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                        No subjects yet.
                        <a href="{{ route('courses.index') }}" class="text-indigo-600">Create or open a course first</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $subjects->links() }}</div>
@endsection
