@extends('layouts.dashboard')

@section('title', 'Pricing Preview')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Pricing preview"
        description="Keep the pricing simple. The landing page is a preview, not a checkout flow."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Overview</x-secondary-button>
            <x-primary-button href="{{ route('super-admin.landing-page.pricing.create') }}">Add pricing item</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <x-dashboard-card title="Plans" description="The public page shows these plans as a preview of the product structure.">
        @if ($pricingItems->isEmpty())
            <x-empty-state
                title="No pricing items yet"
                description="Add a few plans so the homepage can show a friendly preview."
                primary-label="Add first plan"
                primary-href="{{ route('super-admin.landing-page.pricing.create') }}"
                tone="info"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Popular</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Order</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($pricingItems as $pricingItem)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-950">{{ $pricingItem->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $pricingItem->description }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $pricingItem->price_text }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :tone="$pricingItem->is_popular ? 'success' : 'neutral'">{{ $pricingItem->is_popular ? 'Yes' : 'No' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <x-status-badge :tone="$pricingItem->is_active ? 'success' : 'neutral'">{{ $pricingItem->is_active ? 'Active' : 'Hidden' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $pricingItem->sort_order }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('super-admin.landing-page.pricing.edit', $pricingItem) }}" class="cb-btn-secondary !px-3 !py-2">Edit</a>
                                        <form method="POST" action="{{ route('super-admin.landing-page.pricing.destroy', $pricingItem) }}" onsubmit="return confirm('Delete this pricing item?')">
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
            <div class="mt-4">{{ $pricingItems->links() }}</div>
        @endif
    </x-dashboard-card>
</div>
@endsection
