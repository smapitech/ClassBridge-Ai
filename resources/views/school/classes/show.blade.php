@extends('layouts.dashboard')
@section('title', $class->name)
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $class->name }}</h1>
        <p class="text-sm text-gray-500">{{ $class->description ?: 'No description' }} · <span class="px-2 py-0.5 text-xs rounded-full {{ $class->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($class->status) }}</span></p>
        <p class="mt-2 text-xs text-gray-500">Course: <span class="font-medium text-gray-900">{{ $class->course?->name ?? 'Unassigned' }}</span></p>
    </div>
    @unless(Auth::user()->isTeacher())
    <a href="{{ route('school.classes.edit', $class) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit Class</a>
    @endunless
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Teachers -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Teachers ({{ $class->teachers->count() }})</h2>
        @if($class->teachers->count())
            <ul class="divide-y divide-gray-200">
                @foreach($class->teachers as $teacher)
                <li class="py-3 flex items-center justify-between">
                    <div><div class="text-sm font-medium text-gray-900">{{ $teacher->displayName() }}</div><div class="text-xs text-gray-500">{{ $teacher->teacherProfile?->specialization ?? '' }} {{ $teacher->pivot->subject_id ? '· ' . ($allSubjects->find($teacher->pivot->subject_id)?->name ?? 'Subject') : '' }}</div></div>
                    @unless(Auth::user()->isTeacher())
                    <form method="POST" action="{{ route('school.classes.remove-teacher', [$class, $teacher]) }}" onsubmit="return confirm('Remove this teacher?')">@csrf @method('DELETE')<button class="text-red-600 text-sm hover:text-red-900">Remove</button></form>
                    @endunless
                </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-400">No teachers assigned.</p>
        @endif
        @unless(Auth::user()->isTeacher())
        <form method="POST" action="{{ route('school.classes.assign-teacher', $class) }}" class="mt-4 border-t pt-4 flex gap-3">
            @csrf
            <select name="teacher_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                <option value="">Select teacher...</option>
                @foreach($allTeachers as $t)<option value="{{ $t->id }}">{{ $t->displayName() }}</option>@endforeach
            </select>
            <select name="subject_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">— Choose subject —</option>@foreach($allSubjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Assign</button>
        </form>
        @endunless
    </div>

    <!-- Students -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Students ({{ $class->students->count() }})</h2>
        @if($class->students->count())
            <ul class="divide-y divide-gray-200">
                @foreach($class->students as $student)
                <li class="py-3 flex items-center justify-between">
                    <div><div class="text-sm font-medium text-gray-900">{{ $student->displayName() }}</div><div class="text-xs text-gray-500">{{ $student->studentProfile?->admission_number ?? '' }}</div></div>
                    @unless(Auth::user()->isTeacher())
                    <form method="POST" action="{{ route('school.classes.remove-student', [$class, $student]) }}" onsubmit="return confirm('Remove this student?')">@csrf @method('DELETE')<button class="text-red-600 text-sm hover:text-red-900">Remove</button></form>
                    @endunless
                </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-400">No students enrolled.</p>
        @endif
        <form method="POST" action="{{ route('school.classes.assign-student', $class) }}" class="mt-4 border-t pt-4 flex gap-3">
            @csrf
            <select name="student_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                <option value="">Select student...</option>
                @foreach($allStudents as $s)<option value="{{ $s->id }}">{{ $s->displayName() }}</option>@endforeach
            </select>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add</button>
        </form>
    </div>
</div>

<dl class="mt-8 bg-white rounded-xl shadow-sm p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
    <div><dt class="text-gray-500">Level</dt><dd class="font-medium text-gray-900">{{ $class->level ?? '—' }}</dd></div>
    <div><dt class="text-gray-500">Age Group</dt><dd class="font-medium text-gray-900">{{ $class->age_group ?? '—' }}</dd></div>
    <div><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $class->created_at->format('M j, Y') }}</dd></div>
    <div><dt class="text-gray-500">By</dt><dd class="font-medium text-gray-900">{{ $class->creator?->displayName() ?? '—' }}</dd></div>
</dl>
@endsection
