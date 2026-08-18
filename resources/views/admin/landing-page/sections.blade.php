@extends('layouts.dashboard')

@section('title', 'Landing Page Sections')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Sections"
        description="Edit the section copy that supports the homepage story. Keep it short and practical."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Back to overview</x-secondary-button>
            <x-primary-button href="{{ route('home') }}" target="_blank">View live page</x-primary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-2">
        @forelse ($sections as $section)
            <x-dashboard-card :title="$section->title ?? $section->section_key" :description="$section->subtitle">
                <form method="POST" action="{{ route('super-admin.landing-page.sections.update', $section) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge tone="{{ $section->is_active ? 'success' : 'neutral' }}">{{ $section->section_key }}</x-status-badge>
                        <x-status-badge tone="neutral">Sort {{ $section->sort_order ?? 0 }}</x-status-badge>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                            <input name="title" value="{{ old('title', $section->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                            <input name="subtitle" value="{{ old('subtitle', $section->subtitle) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Content</label>
                        <textarea name="content" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('content', $section->content) }}</textarea>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button text</label>
                            <input name="button_text" value="{{ old('button_text', $section->button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button URL</label>
                            <input name="button_url" value="{{ old('button_url', $section->button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button text</label>
                            <input name="secondary_button_text" value="{{ old('secondary_button_text', $section->secondary_button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button URL</label>
                            <input name="secondary_button_url" value="{{ old('secondary_button_url', $section->secondary_button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Image</label>
                            <input name="image" value="{{ old('image', $section->image) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sort order</label>
                            <input name="sort_order" type="number" value="{{ old('sort_order', $section->sort_order ?? 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        </div>
                        <div class="flex items-end">
                            <label class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->is_active)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-semibold text-slate-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Settings JSON</label>
                        <textarea name="settings_json" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('settings_json', json_encode($section->settings ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <x-primary-button type="submit">Save section</x-primary-button>
                        <x-secondary-button href="{{ route('super-admin.landing-page.index') }}">Back</x-secondary-button>
                    </div>
                </form>
            </x-dashboard-card>
        @empty
            <x-empty-state
                title="No sections yet"
                description="The landing page sections table is empty. Run the landing page seeder and refresh."
                primary-label="Back to overview"
                primary-href="{{ route('super-admin.landing-page.index') }}"
                tone="info"
            />
        @endforelse
    </div>
</div>
@endsection
