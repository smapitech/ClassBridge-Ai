@extends('layouts.dashboard')
@section('title', 'AI Generation History')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">AI Generation History</h1>

    <form class="mb-4 flex gap-2 flex-wrap">
        <select name="type" class="rounded border-gray-300 text-xs px-2 py-1"><option value="">All Types</option>@foreach(['curriculum','lesson_plan','examples','quiz','homework','progress_report','general'] as $t)<option value="{{ $t }}" {{ request('type')==$t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>@endforeach</select>
        <select name="status" class="rounded border-gray-300 text-xs px-2 py-1"><option value="">All Status</option><option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option><option value="failed" {{ request('status')=='failed' ? 'selected' : '' }}>Failed</option></select>
        <select name="school_id" class="rounded border-gray-300 text-xs px-2 py-1"><option value="">All Schools</option>@foreach($schools as $s)<option value="{{ $s->id }}" {{ request('school_id')==$s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select>
        <button type="submit" class="rounded bg-gray-100 px-3 py-1 text-xs">Filter</button>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left">Date</th><th class="px-3 py-2 text-left">User</th><th class="px-3 py-2 text-left">Type</th><th class="px-3 py-2 text-left">Title</th><th class="px-3 py-2 text-left">Provider</th><th class="px-3 py-2 text-left">Tokens</th><th class="px-3 py-2 text-left">Status</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($generations as $gen)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-xs">{{ $gen->created_at->format('M d H:i') }}</td>
                    <td class="px-3 py-2 text-xs">{{ $gen->user?->displayName() ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ ucfirst(str_replace('_',' ',$gen->type)) }}</td>
                    <td class="px-3 py-2 text-xs font-medium truncate max-w-xs">{{ $gen->title ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $gen->provider?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $gen->total_tokens ?? '—' }}</td>
                    <td class="px-3 py-2"><span class="rounded-full px-2 py-0.5 text-xs {{ $gen->status==='completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $gen->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $generations->links() }}</div>
</div>
@endsection