@extends('layouts.dashboard')

@section('title', 'Hero Slides')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Hero Slides"
        description="Keep the hero short and human. Rotate through a few lines that explain the product clearly."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Overview</x-secondary-button>
            <x-primary-button href="{{ route('super-admin.landing-page.slides.create') }}">Add slide</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <x-dashboard-card title="Slides" description="Only active slides appear on the public landing page.">
        @if ($slides->isEmpty())
            <x-empty-state
                title="No slides yet"
                description="Create the first hero slide so the homepage can tell the story."
                primary-label="Add first slide"
                primary-href="{{ route('super-admin.landing-page.slides.create') }}"
                tone="info"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Slide</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Headline</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Buttons</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Style</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.2em] text-slate-500">Order</th>
                            <th class="px-6 py-3 text-right text-xs font-black uppercase tracking-[0.2em] text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($slides as $slide)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-950">{{ $slide->label ?? 'Slide' }}</div>
                                    <div class="text-xs text-slate-500">{{ $slide->subtitle }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $slide->headline }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div>{{ $slide->primary_button_text ?? 'None' }}</div>
                                    <div>{{ $slide->secondary_button_text ?? 'None' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $slide->background_style ?? 'Default' }}</td>
                                <td class="px-6 py-4">
                                    <x-status-badge :tone="$slide->is_active ? 'success' : 'neutral'">{{ $slide->is_active ? 'Active' : 'Hidden' }}</x-status-badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $slide->sort_order }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('super-admin.landing-page.slides.edit', $slide) }}" class="cb-btn-secondary !px-3 !py-2">Edit</a>
                                        <form method="POST" action="{{ route('super-admin.landing-page.slides.destroy', $slide) }}" onsubmit="return confirm('Delete this slide?')">
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

            <div class="mt-4">{{ $slides->links() }}</div>
        @endif
    </x-dashboard-card>
</div>
@endsection
