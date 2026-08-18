@extends('layouts.dashboard')
@section('title', 'My Coding Submissions')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="cb-surface mb-6 px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <x-status-badge tone="info">Coding workspace</x-status-badge>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">My Coding Submissions</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">Open your coding work, continue inside the protected live studio, and review your teacher&apos;s feedback in one place.</p>
            </div>
            <x-primary-button href="{{ route('join') }}">
                Join Live Lesson
            </x-primary-button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($submissions->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center">
            <p class="text-gray-500">No coding submissions yet.</p>
            <p class="text-sm text-gray-400 mt-1">Your coding assignments will appear here once your teacher publishes them.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Assignment</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Teacher</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Score</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Submitted</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-900">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($submissions as $submission)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $submission->assignment?->title ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $submission->assignment?->teacher?->displayName() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $submission->status === 'submitted' ? 'bg-yellow-100 text-yellow-700' : ($submission->status === 'reviewed' ? 'bg-green-100 text-green-700' : ($submission->status === 'returned' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium {{ $submission->score !== null ? ($submission->score >= 70 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }}">
                            {{ $submission->score !== null ? $submission->score . '/100' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $submission->submitted_at?->format('M d, Y') ?? 'Not yet' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($submission->assignment)
                                <a href="{{ route('coding.workspace', $submission->assignment) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                    {{ $submission->status === 'draft' ? 'Continue in studio' : 'Open live studio' }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $submissions->links() }}</div>
    @endif
</div>
@endsection
