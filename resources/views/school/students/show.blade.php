@extends('layouts.dashboard')
@section('title', $student->user->displayName())
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-2xl font-bold text-gray-900">{{ $student->user->displayName() }}</h1><p class="text-sm text-gray-500">{{ $student->admission_number ? '#' . $student->admission_number : '' }} · <span class="px-2 py-0.5 text-xs rounded-full {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($student->status) }}</span></p></div>
    @unless(Auth::user()->isTeacher() || Auth::user()->isParent())
    <a href="{{ route('school.students.edit', $student) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
    @endunless
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Profile</h2><dl class="grid grid-cols-2 gap-4 text-sm"><div><dt class="text-gray-500">DOB</dt><dd class="font-medium">{{ $student->date_of_birth?->format('M j, Y') ?? '—' }}</dd></div><div><dt class="text-gray-500">Gender</dt><dd class="font-medium">{{ $student->gender ?? '—' }}</dd></div><div><dt class="text-gray-500">Learning Level</dt><dd class="font-medium">{{ $student->learning_level ?? '—' }}</dd></div><div><dt class="text-gray-500">Class</dt><dd class="font-medium">{{ $student->class?->name ?? '—' }}</dd></div></dl></div>
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Enrolled Classes ({{ $enrolledClasses->count() }})</h2>@forelse($enrolledClasses as $c)<div class="py-2"><a href="{{ route('school.classes.show', $c) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">{{ $c->name }}</a></div>@empty<p class="text-sm text-gray-400">Not enrolled in any class.</p>@endforelse</div>
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Linked Parents ({{ $linkedParents->count() }})</h2>@forelse($linkedParents as $p)<div class="py-2 text-sm"><span class="font-medium text-gray-900">{{ $p->first_name }} {{ $p->last_name }}</span> <span class="text-gray-500">({{ $p->relationship ?? 'Guardian' }})</span> · <span class="text-gray-400">{{ $p->email }}</span></div>@empty<p class="text-sm text-gray-400">No parents linked.</p>@endforelse</div>
</div>
@endsection