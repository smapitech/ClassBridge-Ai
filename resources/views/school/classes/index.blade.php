@extends('layouts.dashboard')
@section('title', 'Classes')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Classes</h1>
        @if(Auth::user()->isTeacher())
            <p class="text-sm text-gray-500">Your assigned classes</p>
        @else
            <p class="text-sm text-gray-500">Manage classes for your school</p>
        @endif
    </div>
    @unless(Auth::user()->isTeacher())
    <a href="{{ route('school.classes.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Class</a>
    @endunless
</div>

@if(request('search'))
    <p class="text-sm text-gray-500 mb-4">Search results for "{{ request('search') }}" — <a href="{{ route('school.classes.index') }}" class="text-indigo-600">Clear</a></p>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teachers</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($classes as $class)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">{{ $class->name }}</div><div class="text-xs text-gray-500">{{ $class->description ? Str::limit($class->description, 40) : '—' }}</div></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class->course?->name ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class->level ?? $class->age_group ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class->teachers_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class->students_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs rounded-full {{ $class->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($class->status) }}</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="{{ route('school.classes.show', $class) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                    @unless(Auth::user()->isTeacher())
                    <a href="{{ route('school.classes.edit', $class) }}" class="text-gray-600 hover:text-gray-900 mr-3">Edit</a>
                    <form method="POST" action="{{ route('school.classes.destroy', $class) }}" class="inline" onsubmit="return confirm('Delete this class?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-900">Delete</button></form>
                    @endunless
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No classes found. @unless(Auth::user()->isTeacher()) <a href="{{ route('school.classes.create') }}" class="text-indigo-600">Create one</a>@endunless</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $classes->links() }}</div>
@endsection
