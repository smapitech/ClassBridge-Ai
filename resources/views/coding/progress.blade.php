@extends('layouts.dashboard')
@section('title', 'Coding Progress Report')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Coding Progress Report</h1>
        <p class="text-sm text-gray-500 mt-1">School-wide overview of coding assignments and student progress.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Total Assignments</p>
                <span class="text-2xl font-bold text-gray-900">{{ $totalAssignments }}</span>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Students Coding</p>
                <span class="text-2xl font-bold text-indigo-600">{{ $studentsWithSubmissions }}</span>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Pending Review</p>
                <span class="text-2xl font-bold text-yellow-600">{{ $pendingSubmissions }}</span>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Graded</p>
                <span class="text-2xl font-bold text-green-600">{{ $gradedSubmissions }}</span>
            </div>
        </div>
    </div>

    {{-- Assignment List --}}
    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-900">Assignments Overview</h2>
        </div>
        @if($assignments->isEmpty())
            <div class="p-8 text-center text-gray-500">No coding assignments found.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Assignment</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Teacher</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Class</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-900">Submissions</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-900">Graded</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-900">Avg Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($assignments as $assignment)
                        @php
                            $submissionCount = $assignment->submissions->count();
                            $gradedCount = $assignment->submissions->where('status', 'reviewed')->count();
                            $avgScore = $assignment->submissions->avg('score');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $assignment->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $assignment->teacher?->displayName() ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $assignment->classe?->name ?? 'All' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $assignment->status === 'published' ? 'bg-green-100 text-green-700' : ($assignment->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $submissionCount }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $gradedCount }}</td>
                            <td class="px-4 py-3 text-center {{ $avgScore ? ($avgScore >= 70 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }} font-medium">
                                {{ $avgScore ? number_format($avgScore, 1) . '%' : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection