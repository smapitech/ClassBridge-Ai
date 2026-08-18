@extends('layouts.dashboard')

@section('title', 'Audiences')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Audiences"
        description="These cards help people see quickly whether ClassBridge fits their setup."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Overview</x-secondary-button>
            <x-primary-button href="{{ route('super-admin.landing-page.audiences.create') }}">Add audience</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <x-dashboard-card title="Audience cards" description="Keep the cards short and practical.">
        @if ($audiences->isEmpty())
            <x-empty-state
                title="No audience cards yet"
                description="Add the people you want the homepage to speak to."
                primary-label="Add first audience"
                primary-href="{{ route('super-admin.landing-page.audiences.create') }}"
                tone="info"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Audience</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Order</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($audiences as $audience)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-950">{{ $audience->title }}</div>
                                    <div class="text-xs text-slate-500">{{ $audience->icon ?? 'No icon' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $audience->description }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :tone="$audience->is_active ? 'success' : 'neutral'">{{ $audience->is_active ? 'Active' : 'Hidden' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $audience->sort_order }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('super-admin.landing-page.audiences.edit', $audience) }}" class="cb-btn-secondary !px-3 !py-2">Edit</a>
                                        <form method="POST" action="{{ route('super-admin.landing-page.audiences.destroy', $audience) }}" onsubmit="return confirm('Delete this audience card?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="cb-btn-secondary !border-rose-200 !bg-rose-50 !text-rose-700 hover:!bg-rose-100 !px-3 !py-2" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $audiences->links() }}</div>
        @endif
    </x-dashboard-card>
</div>
@endsection
