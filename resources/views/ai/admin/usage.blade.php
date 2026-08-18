@extends('layouts.dashboard')
@section('title', 'AI Usage Logs')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">AI Usage Logs</h1>
    <div class="grid grid-cols-3 gap-4 mb-6 text-sm">
        <div class="rounded-lg border bg-white p-3 text-center"><span class="text-2xl font-bold text-gray-900">{{ number_format($totalTokens) }}</span><p class="text-xs text-gray-500">Total Tokens</p></div>
        <div class="rounded-lg border bg-white p-3 text-center"><span class="text-2xl font-bold text-gray-900">{{ number_format($logs->total()) }}</span><p class="text-xs text-gray-500">Total Requests</p></div>
        <div class="rounded-lg border bg-white p-3 text-center"><span class="text-2xl font-bold text-green-600">${{ number_format($totalCost, 4) }}</span><p class="text-xs text-gray-500">Est. Total Cost</p></div>
    </div>

    <form class="mb-4 flex gap-2 flex-wrap">
        <select name="school_id" class="rounded border-gray-300 text-xs px-2 py-1"><option value="">All Schools</option>@foreach($schools as $s)<option value="{{ $s->id }}" {{ request('school_id')==$s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select>
        <select name="request_status" class="rounded border-gray-300 text-xs px-2 py-1"><option value="">All Status</option><option value="success" {{ request('request_status')=='success' ? 'selected' : '' }}>Success</option><option value="failed" {{ request('request_status')=='failed' ? 'selected' : '' }}>Failed</option></select>
        <button type="submit" class="rounded bg-gray-100 px-3 py-1 text-xs">Filter</button>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Date</th><th class="px-3 py-2 text-left">School</th><th class="px-3 py-2 text-left">User</th><th class="px-3 py-2 text-left">Provider</th><th class="px-3 py-2 text-left">Model</th><th class="px-3 py-2 text-left">Tokens</th><th class="px-3 py-2 text-left">Cost</th><th class="px-3 py-2 text-left">Status</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-xs">{{ $log->created_at->format('M d H:i') }}</td>
                    <td class="px-3 py-2 text-xs">{{ $log->school?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $log->user?->displayName() ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $log->provider?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs font-mono">{{ $log->model ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $log->total_tokens ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">${{ number_format($log->cost_estimate ?? 0, 6) }}</td>
                    <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs {{ $log->request_status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $log->request_status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection