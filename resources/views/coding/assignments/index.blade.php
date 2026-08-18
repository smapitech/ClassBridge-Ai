@extends('layouts.dashboard')
@section('title', 'Coding Assignments')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="cb-surface mb-6 px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <x-status-badge tone="info">Coding hub</x-status-badge>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Coding Assignments and Live Studio</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Create assignments, then open the protected live coding workspace where teacher and student can edit together in real time.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('join') }}">
                    Join Live Lesson
                </x-primary-button>
                <x-secondary-button href="{{ route('coding.assignments.create') }}">
                    + New Assignment
                </x-secondary-button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('coding.assignments.index') }}" class="rounded-full px-3 py-1 text-xs font-medium {{ !request('status') ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">All</a>
        <a href="{{ route('coding.assignments.index', ['status'=>'draft']) }}" class="rounded-full px-3 py-1 text-xs font-medium {{ request('status')==='draft' ? 'bg-gray-200 text-gray-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Draft</a>
        <a href="{{ route('coding.assignments.index', ['status'=>'published']) }}" class="rounded-full px-3 py-1 text-xs font-medium {{ request('status')==='published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Published</a>
        <a href="{{ route('coding.assignments.index', ['status'=>'closed']) }}" class="rounded-full px-3 py-1 text-xs font-medium {{ request('status')==='closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Closed</a>
    </div>

    @if($assignments->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center">
            <p class="text-gray-500">No coding assignments yet.</p>
            <a href="{{ route('coding.assignments.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">Create your first assignment</a>
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Title</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Class</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Submissions</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Due</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($assignments as $assignment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $assignment->title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $assignment->classe?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $assignment->status === 'published' ? 'bg-green-100 text-green-700' : ($assignment->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $assignment->submissions->count() }} submitted
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $assignment->due_at?->format('M d, Y') ?? 'No due date' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('coding.review', $assignment) }}" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 hover:bg-gray-200">Review</a>
                                <a href="{{ route('coding.assignments.edit', $assignment) }}" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700 hover:bg-gray-200">Edit</a>
                                <a href="{{ route('coding.assignments.preview', $assignment) }}" class="rounded bg-indigo-50 px-2 py-1 text-xs text-indigo-600 hover:bg-indigo-100">Preview</a>
                                <a href="{{ route('join') }}" class="rounded bg-sky-50 px-2 py-1 text-xs text-sky-600 hover:bg-sky-100">Live Lesson</a>
                                <form method="POST" action="{{ route('coding.assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this assignment?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="rounded bg-red-50 px-2 py-1 text-xs text-red-600 hover:bg-red-100">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
