@extends('layouts.dashboard')

@section('title', 'Web Builder Blocks')

@section('content')
@php
    $section = fn (string $key) => $sections->get($key);

    $toLinkLines = function ($items) {
        return collect($items ?? [])
            ->map(fn ($item) => trim((string) data_get($item, 'label', data_get($item, 'title', ''))) . '|' . trim((string) data_get($item, 'url', '')))
            ->filter(fn ($line) => trim(str_replace('|', '', $line)) !== '')
            ->implode("\n");
    };

    $toSimpleLines = function ($items) {
        return collect($items ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->implode("\n");
    };

    $toStepLines = function ($items) {
        return collect($items ?? [])
            ->map(fn ($item) => trim((string) data_get($item, 'title', '')) . '|' . trim((string) data_get($item, 'copy', '')))
            ->filter(fn ($line) => trim(str_replace('|', '', $line)) !== '')
            ->implode("\n");
    };

    $toStatsLines = function ($items) {
        return collect($items ?? [])
            ->map(fn ($item) => trim((string) data_get($item, 'value', '')) . '|' . trim((string) data_get($item, 'label', '')))
            ->filter(fn ($line) => trim(str_replace('|', '', $line)) !== '')
            ->implode("\n");
    };

    $toTestimonialLines = function ($items) {
        return collect($items ?? [])
            ->map(fn ($item) => trim((string) data_get($item, 'quote', '')) . '|' . trim((string) data_get($item, 'name', '')) . '|' . trim((string) data_get($item, 'role', '')))
            ->filter(fn ($line) => trim(str_replace('|', '', $line)) !== '')
            ->implode("\n");
    };

    $sectionsNav = [
        'site_header' => 'Header',
        'hero' => 'Hero',
        'demo_preview' => 'Live preview',
        'features' => 'Features',
        'how_it_works' => 'How it works',
        'social_proof' => 'Social proof',
        'pricing_preview' => 'Pricing preview',
        'request_demo' => 'Request demo',
        'site_footer' => 'Footer',
    ];
@endphp

<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Web Builder"
        title="Homepage Blocks"
        description="Edit the public page as simple blocks. Each block updates the live homepage immediately after save."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Overview</x-secondary-button>
            <x-secondary-button href="{{ route('home') }}" target="_blank">View live homepage</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="flex flex-wrap gap-2 rounded-3xl border border-slate-200 bg-white p-3 shadow-sm">
        @foreach ($sectionsNav as $key => $label)
            <a href="#{{ $key }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300 hover:bg-slate-50">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-dashboard-card id="site_header" title="Header" description="Logo, top nav, and the first buttons users see.">
            @php $header = $section('site_header'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $header) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Brand name</label>
                        <input name="title" value="{{ old('title', $header->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Tagline</label>
                        <input name="subtitle" value="{{ old('subtitle', $header->subtitle) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button text</label>
                        <input name="button_text" value="{{ old('button_text', $header->button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button URL</label>
                        <input name="button_url" value="{{ old('button_url', $header->button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button text</label>
                        <input name="secondary_button_text" value="{{ old('secondary_button_text', $header->secondary_button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button URL</label>
                        <input name="secondary_button_url" value="{{ old('secondary_button_url', $header->secondary_button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Navigation links</label>
                    <textarea
                        name="nav_links_text"
                        rows="5"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="Live classroom|#demo&#10;Who it helps|#for-who"
                    >{{ old('nav_links_text', $toLinkLines(data_get($header, 'settings.nav_links', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One link per line. Use <code>Label|URL</code>.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save header</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="hero" title="Hero" description="The main headline, CTA buttons, and live classroom preview labels.">
            @php $hero = $section('hero'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $hero) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Headline line 1</label>
                        <input name="title" value="{{ old('title', $hero->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Headline accent line</label>
                        <input name="content" value="{{ old('content', $hero->content) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subheadline</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $hero->subtitle) }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button text</label>
                        <input name="button_text" value="{{ old('button_text', $hero->button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button URL</label>
                        <input name="button_url" value="{{ old('button_url', $hero->button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button text</label>
                        <input name="secondary_button_text" value="{{ old('secondary_button_text', $hero->secondary_button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button URL</label>
                        <input name="secondary_button_url" value="{{ old('secondary_button_url', $hero->secondary_button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Eyebrow</label>
                        <input name="eyebrow" value="{{ old('eyebrow', data_get($hero, 'settings.eyebrow')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Room code</label>
                        <input name="room_code" value="{{ old('room_code', data_get($hero, 'settings.room_code')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Mode label</label>
                        <input name="mode_label" value="{{ old('mode_label', data_get($hero, 'settings.mode_label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Status label</label>
                        <input name="status_label" value="{{ old('status_label', data_get($hero, 'settings.status_label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Badge one</label>
                        <input name="badge_one_text" value="{{ old('badge_one_text', data_get($hero, 'settings.badge_one_text')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Badge two</label>
                        <input name="badge_two_text" value="{{ old('badge_two_text', data_get($hero, 'settings.badge_two_text')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Hero chips</label>
                    <textarea
                        name="hero_chips_text"
                        rows="4"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="Schools&#10;Private tutors"
                    >{{ old('hero_chips_text', $toSimpleLines(data_get($hero, 'settings.chips', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One chip per line.</p>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Hero code lines</label>
                    <textarea
                        name="hero_code_lines_text"
                        rows="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="// Teacher guides the learner live&#10;room = 'CB-2147'"
                    >{{ old('hero_code_lines_text', $toSimpleLines(data_get($hero, 'settings.code_lines', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One line per row. Keep it short.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save hero</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="demo_preview" title="Live preview" description="The video or walkthrough panel below the hero.">
            @php $demo = $section('demo_preview'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $demo) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input name="title" value="{{ old('title', $demo->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $demo->subtitle) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Section label</label>
                        <input name="section_label" value="{{ old('section_label', data_get($demo, 'settings.label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Video label</label>
                        <input name="video_label" value="{{ old('video_label', data_get($demo, 'settings.video_label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Button text</label>
                        <input name="button_text" value="{{ old('button_text', $demo->button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Button URL</label>
                        <input name="button_url" value="{{ old('button_url', $demo->button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save preview</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="features" title="Features" description="The heading above the homepage feature cards.">
            @php $features = $section('features'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $features) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Section label</label>
                        <input name="section_label" value="{{ old('section_label', data_get($features, 'settings.label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input name="title" value="{{ old('title', $features->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $features->subtitle) }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save features</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="how_it_works" title="How it works" description="Short steps that explain the lesson flow.">
            @php $how = $section('how_it_works'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $how) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input name="title" value="{{ old('title', $how->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $how->subtitle) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Section label</label>
                        <input name="section_label" value="{{ old('section_label', data_get($how, 'settings.label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Content</label>
                        <textarea name="content" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('content', $how->content) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Steps</label>
                    <textarea
                        name="steps_text"
                        rows="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="Teacher creates the lesson|Open one protected room and choose a mode.&#10;Student joins by code|The learner enters the same classroom."
                    >{{ old('steps_text', $toStepLines(data_get($how, 'settings.steps', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One line per step. Use <code>Title|Copy</code>.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save steps</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="social_proof" title="Social proof" description="Numbers and testimonials that help people trust the product.">
            @php $social = $section('social_proof'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $social) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input name="title" value="{{ old('title', $social->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $social->subtitle) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Section label</label>
                        <input name="section_label" value="{{ old('section_label', data_get($social, 'settings.label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Content</label>
                        <textarea name="content" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('content', $social->content) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Stats</label>
                    <textarea
                        name="stats_text"
                        rows="5"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="1|Protected room per lesson&#10;5|Teaching modes"
                    >{{ old('stats_text', $toStatsLines(data_get($social, 'settings.stats', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One line per stat. Use <code>Value|Label</code>.</p>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Testimonials</label>
                    <textarea
                        name="testimonials_text"
                        rows="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="It feels calm and personal.|Private tutor|One-to-one teaching"
                    >{{ old('testimonials_text', $toTestimonialLines(data_get($social, 'settings.testimonials', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One line per testimonial. Use <code>Quote|Name|Role</code>.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save proof</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="pricing_preview" title="Pricing preview" description="The heading above the pricing cards.">
            @php $pricing = $section('pricing_preview'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $pricing) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Section label</label>
                        <input name="section_label" value="{{ old('section_label', data_get($pricing, 'settings.label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input name="title" value="{{ old('title', $pricing->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $pricing->subtitle) }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save pricing</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="request_demo" title="Request demo" description="The call to action and form copy on the homepage.">
            @php $request = $section('request_demo'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $request) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input name="title" value="{{ old('title', $request->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                        <textarea name="subtitle" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('subtitle', $request->subtitle) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Section label</label>
                        <input name="section_label" value="{{ old('section_label', data_get($request, 'settings.label')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Form title</label>
                        <input name="form_title" value="{{ old('form_title', data_get($request, 'settings.form_title')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Form subtitle</label>
                        <input name="form_subtitle" value="{{ old('form_subtitle', data_get($request, 'settings.form_subtitle')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Badge text</label>
                        <input name="badge_text" value="{{ old('badge_text', data_get($request, 'settings.badge_text')) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Button text</label>
                        <input name="button_text" value="{{ old('button_text', $request->button_text) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Button URL</label>
                        <input name="button_url" value="{{ old('button_url', $request->button_url) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Content</label>
                        <textarea name="content" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('content', $request->content) }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save request block</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>

        <x-dashboard-card id="site_footer" title="Footer" description="Brand note and links shown at the bottom of the homepage.">
            @php $footer = $section('site_footer'); @endphp
            <form method="POST" action="{{ route('super-admin.web-builder.sections.update', $footer) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Brand title</label>
                        <input name="title" value="{{ old('title', $footer->title) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Tagline</label>
                        <input name="subtitle" value="{{ old('subtitle', $footer->subtitle) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Content</label>
                        <textarea name="content" rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('content', $footer->content) }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Footer links</label>
                    <textarea
                        name="footer_links_text"
                        rows="5"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 font-mono text-sm"
                        placeholder="Features|#features&#10;Pricing|#pricing"
                    >{{ old('footer_links_text', $toLinkLines(data_get($footer, 'settings.links', []))) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">One line per link. Use <code>Label|URL</code>.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-primary-button type="submit">Save footer</x-primary-button>
                    <x-secondary-button href="{{ route('super-admin.web-builder.index') }}">Back</x-secondary-button>
                </div>
            </form>
        </x-dashboard-card>
    </div>
</div>
@endsection
