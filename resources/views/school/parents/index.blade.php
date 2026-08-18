@extends('layouts.dashboard')
@section('title', 'Parents')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Parents</h1>
    <a href="{{ route('school.parents.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Parent</a>
</div>
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Relationship</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($parents as $p)
            <tr class="hover:bg-gray-50"><td class="px-6 py-4"><div class="text-sm font-medium text-gray-900">{{ $p->user->displayName() }}</div></td><td class="px-6 py-4 text-sm text-gray-500">{{ $p->user->email }}</td><td class="px-6 py-4 text-sm text-gray-500">{{ $p->relationship ?? '—' }}</td><td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $p->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($p->status) }}</span></td><td class="px-6 py-4 text-right text-sm"><a href="{{ route('school.parents.show', $p) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a><a href="{{ route('school.parents.edit', $p) }}" class="text-gray-600 hover:text-gray-900 mr-3">Edit</a><form method="POST" action="{{ route('school.parents.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-900">Delete</button></form></td></tr>
            @empty
            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No parents. <a href="{{ route('school.parents.create') }}" class="text-indigo-600">Add one</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $parents->links() }}</div>
@endsection