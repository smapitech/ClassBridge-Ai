@extends('layouts.dashboard')
@section('title', $teacher->user->displayName())
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-2xl font-bold text-gray-900">{{ $teacher->user->displayName() }}</h1><p class="text-sm text-gray-500">{{ $teacher->user->email }} · <span class="px-2 py-0.5 text-xs rounded-full {{ $teacher->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($teacher->status) }}</span></p></div>
    <a href="{{ route('school.teachers.edit', $teacher) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Profile</h2><dl class="grid grid-cols-2 gap-4 text-sm"><div><dt class="text-gray-500">Qualification</dt><dd class="font-medium">{{ $teacher->qualification ?? '—' }}</dd></div><div><dt class="text-gray-500">Specialization</dt><dd class="font-medium">{{ $teacher->specialization ?? '—' }}</dd></div><div><dt class="text-gray-500">Experience</dt><dd class="font-medium">{{ $teacher->years_of_experience ? $teacher->years_of_experience . ' years' : '—' }}</dd></div><div><dt class="text-gray-500">Bio</dt><dd class="font-medium">{{ $teacher->bio ?? '—' }}</dd></div></dl></div>
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Assigned Classes ({{ $assignedClasses->count() }})</h2>@forelse($assignedClasses as $c)<div class="py-2"><a href="{{ route('school.classes.show', $c) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">{{ $c->name }}</a></div>@empty<p class="text-sm text-gray-400">No classes assigned.</p>@endforelse</div>
</div>
@endsection