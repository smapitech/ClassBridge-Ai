@extends('layouts.dashboard')
@section('title', 'Students')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Students</h1>@if(Auth::user()->isParent())<p class="text-sm text-gray-500">Your children</p>@endif</div>
    @unless(Auth::user()->isTeacher() || Auth::user()->isParent())
    <a href="{{ route('school.students.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Student</a>
    @endunless
</div>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admission #</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($students as $s)
            <tr class="hover:bg-gray-50"><td class="px-6 py-4"><div class="text-sm font-medium text-gray-900">{{ $s->user->displayName() }}</div></td><td class="px-6 py-4 text-sm text-gray-500">{{ $s->admission_number ?? '—' }}</td><td class="px-6 py-4 text-sm text-gray-500">{{ $s->gender ?? '—' }}</td><td class="px-6 py-4 text-sm text-gray-500">{{ $s->learning_level ?? '—' }}</td><td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $s->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($s->status) }}</span></td><td class="px-6 py-4 text-right text-sm">
            <a href="{{ route('school.students.show', $s) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
            @unless(Auth::user()->isTeacher() || Auth::user()->isParent())
            <a href="{{ route('school.students.edit', $s) }}" class="text-gray-600 hover:text-gray-900 mr-3">Edit</a>
            <form method="POST" action="{{ route('school.students.destroy', $s) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-900">Delete</button></form>
            @endunless</td></tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No students. @unless(Auth::user()->isTeacher() || Auth::user()->isParent())<a href="{{ route('school.students.create') }}" class="text-indigo-600">Add one</a>@endunless</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $students->links() }}</div>
@endsection