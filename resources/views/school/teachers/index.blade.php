@extends('layouts.dashboard')
@section('title', 'Teachers')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Teachers</h1>
    <a href="{{ route('school.teachers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Teacher</a>
</div>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Specialization</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($teachers as $t)
            <tr class="hover:bg-gray-50"><td class="px-6 py-4"><div class="text-sm font-medium text-gray-900">{{ $t->user->displayName() }}</div></td><td class="px-6 py-4 text-sm text-gray-500">{{ $t->user->email }}</td><td class="px-6 py-4 text-sm text-gray-500">{{ $t->specialization ?? '—' }}</td><td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $t->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($t->status) }}</span></td><td class="px-6 py-4 text-right text-sm"><a href="{{ route('school.teachers.show', $t) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a><a href="{{ route('school.teachers.edit', $t) }}" class="text-gray-600 hover:text-gray-900 mr-3">Edit</a><form method="POST" action="{{ route('school.teachers.destroy', $t) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-900">Delete</button></form></td></tr>
            @empty
            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No teachers. <a href="{{ route('school.teachers.create') }}" class="text-indigo-600">Add one</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $teachers->links() }}</div>
@endsection