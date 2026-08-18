@extends('layouts.dashboard')

@section('title', 'Features')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Features"
        description="Keep the feature list short. The homepage should feel practical, not crowded."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Overview</x-secondary-button>
            <x-primary-button href="{{ route('super-admin.landing-page.features.create') }}">Add feature</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <x-dashboard-card title="Feature list" description="These items can appear as compact chips or highlight cards on the homepage.">
        @if ($features->isEmpty())
            <x-empty-state
                title="No features yet"
                description="Add the key product stories you want on the homepage."
                primary-label="Add first feature"
                primary-href="{{ route('super-admin.landing-page.features.create') }}"
                tone="info"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Feature</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Link</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Order</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($features as $feature)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-950">{{ $feature->title }}</div>
                                    <div class="text-xs text-slate-500">{{ $feature->description }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ match ($feature->feature_group) {
                                        'core' => 'Core',
                                        'ai' => 'AI',
                                        'learning' => 'Learning',
                                        'family' => 'Family',
                                        'business' => 'Business',
                                        default => 'General',
                                    } }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $feature->link_text ?? 'None' }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :tone="$feature->is_active ? 'success' : 'neutral'">{{ $feature->is_active ? 'Active' : 'Hidden' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $feature->sort_order }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('super-admin.landing-page.features.edit', $feature) }}" class="cb-btn-secondary !px-3 !py-2">Edit</a>
                                        <form method="POST" action="{{ route('super-admin.landing-page.features.destroy', $feature) }}" onsubmit="return confirm('Delete this feature?')">
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
            <div class="mt-4">{{ $features->links() }}</div>
        @endif
    </x-dashboard-card>
</div>
@endsection
