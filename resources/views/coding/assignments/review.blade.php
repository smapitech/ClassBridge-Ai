@extends('layouts.dashboard')
@section('title', 'Review: ' . $assignment->title)

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="cb-surface mb-6 px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <a href="{{ route('coding.assignments.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Back to assignments</a>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Review Submissions</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $assignment->title }} {{ $assignment->classe ? ' - ' . $assignment->classe->name : '' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('join') }}">
                    Join Live Lesson
                </x-primary-button>
                <x-secondary-button href="{{ route('coding.assignments.preview', $assignment) }}">
                    Preview Assignment
                </x-secondary-button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $assignment->submissions->count() }}</p>
            <p class="text-xs text-gray-500">Total Submissions</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $assignment->submissions->where('status', 'submitted')->count() }}</p>
            <p class="text-xs text-gray-500">Pending</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $assignment->submissions->where('status', 'reviewed')->count() }}</p>
            <p class="text-xs text-gray-500">Reviewed</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($assignment->submissions->avg('score') ?? 0, 1) }}%</p>
            <p class="text-xs text-gray-500">Avg Score</p>
        </div>
    </div>

    @if($assignment->submissions->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center">
            <p class="text-gray-500">No submissions yet.</p>
            <p class="text-sm text-gray-400">Students will appear here once they submit their work.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Student</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Submitted</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Score</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-900">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($assignment->submissions as $submission)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $submission->student?->displayName() ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-400">{{ $submission->student?->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $submission->status === 'submitted' ? 'bg-yellow-100 text-yellow-700' : ($submission->status === 'reviewed' ? 'bg-green-100 text-green-700' : ($submission->status === 'returned' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ ucfirst($submission->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $submission->submitted_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium {{ $submission->score !== null ? ($submission->score >= 70 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }}">
                            {{ $submission->score !== null ? $submission->score . '/100' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('coding.submission.view', $submission) }}" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                    Review Code
                                </a>
                                <a href="{{ route('coding.workspace', ['assignment' => $assignment, 'student_id' => $submission->student_id]) }}" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                    Open live studio
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
