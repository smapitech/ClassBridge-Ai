@extends('layouts.dashboard')

@section('title', 'Demo Requests')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Demo Requests"
        description="People who want a walkthrough of ClassBridge AI land here. Keep the follow-up simple and human."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Landing page</x-secondary-button>
            <x-primary-button href="{{ route('home') }}" target="_blank">View live page</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['New', $stats['new'], 'warning'],
            ['Contacted', $stats['contacted'], 'info'],
            ['Closed', $stats['closed'], 'success'],
        ] as $metric)
            <x-dashboard-card>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ $metric[0] }}</p>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <p class="text-3xl font-black text-slate-950">{{ $metric[1] }}</p>
                    <x-status-badge :tone="$metric[2]">{{ $metric[0] }}</x-status-badge>
                </div>
            </x-dashboard-card>
        @endforeach
    </div>

    <x-dashboard-card title="Filter" description="Narrow the list when you are working through follow-up.">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('super-admin.demo-requests.index') }}" class="cb-btn-secondary {{ request('status') ? '' : '!border-slate-950 !bg-slate-950 !text-white' }}">All</a>
            @foreach (['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'] as $value => $label)
                <a href="{{ route('super-admin.demo-requests.index', ['status' => $value]) }}" class="cb-btn-secondary {{ request('status') === $value ? '!border-slate-950 !bg-slate-950 !text-white' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </x-dashboard-card>

    <x-dashboard-card title="Latest requests" description="Every request keeps the same calm, practical tone as the homepage.">
        @if ($demoRequests->isEmpty())
            <x-empty-state
                title="No demo requests yet"
                description="When someone submits the landing page form, their request will appear here."
                primary-label="View landing page"
                primary-href="{{ route('home') }}"
                tone="info"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Requested</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($demoRequests as $request)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-950">{{ $request->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $request->message ? \Illuminate\Support\Str::limit($request->message, 80) : 'No message' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div>{{ $request->email }}</div>
                                    <div>{{ $request->phone ?? 'No phone' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $request->organization ?? 'Not provided' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $request->role_type ? \Illuminate\Support\Str::headline($request->role_type) : 'Not provided' }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :tone="$request->status === 'contacted' ? 'info' : ($request->status === 'closed' ? 'success' : 'warning')">
                                        {{ ucfirst($request->status) }}
                                    </x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $request->created_at?->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('super-admin.demo-requests.status', $request) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                                            @foreach (['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'] as $value => $label)
                                                <option value="{{ $value }}" @selected($request->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <x-primary-button type="submit">Update</x-primary-button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $demoRequests->links() }}
            </div>
        @endif
    </x-dashboard-card>
</div>
@endsection
