@extends('layouts.dashboard')
@section('title', 'Organizations Management')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Organizations</h1>
        <p class="text-sm text-gray-500">Manage schools, tutoring centers, academies, and private tutor workspaces.</p>
    </div>
    <a href="{{ route('super-admin.schools.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Organization</a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($schools as $school)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $school->displayLabel() }}</div>
                    <div class="text-xs text-gray-500">{{ $school->slug }} · {{ $school->city ?? 'N/A' }}, {{ $school->country ?? '' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ classbridge_organization_type_label($school->organization_type) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $school->owner?->displayName() ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $school->subscriptionPlan?->name ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $school->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $school->status === 'trial' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $school->status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $school->status === 'inactive' ? 'bg-gray-100 text-gray-600' : '' }}">
                        {{ ucfirst($school->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $school->users_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="{{ route('super-admin.schools.edit', $school) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                    <form method="POST" action="{{ route('super-admin.schools.toggle-suspend', $school) }}" class="inline">
                        @csrf @method('PUT')
                        <button type="submit" class="{{ $school->status === 'suspended' ? 'text-green-600 hover:text-green-900' : 'text-yellow-600 hover:text-yellow-900' }} mr-3">
                            {{ $school->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('super-admin.schools.destroy', $school) }}" class="inline" onsubmit="return confirm('Delete this organization?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No organizations registered yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $schools->links() }}</div>
@endsection
