@extends('layouts.dashboard')

@section('title', 'Web Builder')

@section('content')
@php
    $headerSection = $sections->get('site_header');
    $heroSection = $sections->get('hero');
    $demoSection = $sections->get('demo_preview');
    $howSection = $sections->get('how_it_works');
    $socialSection = $sections->get('social_proof');
    $requestSection = $sections->get('request_demo');
    $footerSection = $sections->get('site_footer');

    $heroHeadline = $heroSection?->title ?? 'Teach online like you are sitting beside the child -';
    $heroAccent = $heroSection?->content ?? 'without remote access risk.';
    $heroSubtitle = $heroSection?->subtitle ?? 'ClassBridge AI gives schools, tutors, and online teachers a protected live classroom where teacher and student can write, draw, code, point, explain, and learn together in real time.';

    $editableBlocks = [
        ['key' => 'site_header', 'label' => 'Header', 'description' => 'Logo, nav links, and top buttons.'],
        ['key' => 'hero', 'label' => 'Hero', 'description' => 'Main headline, chips, and hero visual labels.'],
        ['key' => 'demo_preview', 'label' => 'Live preview', 'description' => 'Video section title and copy.'],
        ['key' => 'features', 'label' => 'Features', 'description' => 'The label and text above the feature cards.'],
        ['key' => 'how_it_works', 'label' => 'How it works', 'description' => 'The three-step teaching flow.'],
        ['key' => 'social_proof', 'label' => 'Social proof', 'description' => 'Stats and testimonials.'],
        ['key' => 'pricing_preview', 'label' => 'Pricing preview', 'description' => 'The heading above the pricing cards.'],
        ['key' => 'request_demo', 'label' => 'Request demo', 'description' => 'CTA text and support copy.'],
        ['key' => 'site_footer', 'label' => 'Footer', 'description' => 'Brand note and footer links.'],
    ];
@endphp

<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Web Builder"
        title="Web Builder"
        description="Edit the public homepage from one place. Keep the story short, warm, and practical for parents, tutors, and schools."
    >
        <x-slot:actions>
            <x-primary-button href="{{ route('super-admin.web-builder.sections') }}">Edit blocks</x-primary-button>
            <x-secondary-button href="{{ route('home') }}" target="_blank">View live homepage</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Slides', $stats['slides'], 'info'],
            ['Features', $stats['features'], 'success'],
            ['Audiences', $stats['audiences'], 'purple'],
            ['Pricing', $stats['pricing'], 'warning'],
            ['Blocks', $stats['sections'], 'neutral'],
            ['Demo requests', $stats['demo_requests'], 'danger'],
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

    <div class="grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
        <x-dashboard-card title="Homepage blueprint" description="This is the public page structure the builder controls.">
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($editableBlocks as $block)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ $block['key'] }}</p>
                                <h3 class="mt-1 font-black text-slate-950">{{ $block['label'] }}</h3>
                            </div>
                            <x-status-badge tone="{{ $sections->get($block['key'])?->is_active ? 'success' : 'neutral' }}">
                                {{ $sections->get($block['key'])?->is_active ? 'Active' : 'Hidden' }}
                            </x-status-badge>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $block['description'] }}</p>
                        <div class="mt-4">
                            <a href="{{ route('super-admin.web-builder.sections') }}#{{ $block['key'] }}" class="cb-btn-secondary !px-3 !py-2">Edit block</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-dashboard-card>

        <x-dashboard-card title="Quick actions" description="Jump into the homepage areas that change the first impression.">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-quick-action-card href="{{ route('super-admin.web-builder.slides.index') }}" tone="info" badge="Hero" title="Manage hero slides" description="Control the rotating message and call to action." />
                <x-quick-action-card href="{{ route('super-admin.web-builder.features.index') }}" tone="success" badge="Core" title="Manage features" description="Keep the product benefits short and visible." />
                <x-quick-action-card href="{{ route('super-admin.web-builder.audiences.index') }}" tone="purple" badge="People" title="Manage audiences" description="Shape the cards for tutors, parents, and schools." />
                <x-quick-action-card href="{{ route('super-admin.web-builder.pricing.index') }}" tone="warning" badge="Plans" title="Manage pricing" description="Keep the plans simple and easy to compare." />
                <x-quick-action-card href="{{ route('super-admin.web-builder.sections') }}" tone="neutral" badge="Blocks" title="Edit page blocks" description="Update the header, hero, proof, and footer copy." />
                <x-quick-action-card href="{{ route('super-admin.web-builder.demo-requests.index') }}" tone="danger" badge="Leads" title="Review demo requests" description="See who wants a guided walkthrough." />
            </div>
        </x-dashboard-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <x-dashboard-card title="Hero preview" description="The first message visitors see on the homepage.">
            <div class="rounded-[1.75rem] border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.08),_transparent_32%),radial-gradient(circle_at_top_right,_rgba(16,185,129,0.08),_transparent_30%),linear-gradient(135deg,_#ffffff_0%,_#f8fafc_100%)] p-6">
                <p class="text-xs font-black uppercase tracking-[0.28em] text-sky-600">Live interactive learning</p>
                <h3 class="mt-3 max-w-2xl text-3xl font-black tracking-tight text-slate-950">{{ $heroHeadline }}</h3>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    {{ $heroAccent }}
                </p>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    {{ $heroSubtitle }}
                </p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach (collect(data_get($heroSection, 'settings.chips', []))->take(6) as $chip)
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>
        </x-dashboard-card>

        <x-dashboard-card title="Current page mix" description="A quick glance at the homepage content and its current state.">
            <div class="space-y-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Header</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $headerSection?->title ?? 'ClassBridge AI' }}</p>
                        </div>
                        <x-status-badge :tone="$headerSection?->is_active ? 'success' : 'neutral'">{{ $headerSection?->is_active ? 'Live' : 'Hidden' }}</x-status-badge>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live preview</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $demoSection?->title ?? 'A full learning environment in your browser' }}</p>
                        </div>
                        <x-status-badge :tone="$demoSection?->is_active ? 'success' : 'neutral'">{{ $demoSection?->is_active ? 'Live' : 'Hidden' }}</x-status-badge>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Footer</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $footerSection?->title ?? 'ClassBridge AI' }}</p>
                        </div>
                        <x-status-badge :tone="$footerSection?->is_active ? 'success' : 'neutral'">{{ $footerSection?->is_active ? 'Live' : 'Hidden' }}</x-status-badge>
                    </div>
                </div>
            </div>
        </x-dashboard-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-dashboard-card title="Recent live content" description="The public content currently saved in the builder.">
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($sections->take(6) as $section)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ $section->section_key }}</p>
                                <h3 class="mt-1 font-black text-slate-950">{{ $section->title ?? 'Untitled block' }}</h3>
                            </div>
                            <x-status-badge :tone="$section->is_active ? 'success' : 'neutral'">{{ $section->is_active ? 'Active' : 'Hidden' }}</x-status-badge>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $section->subtitle ?? $section->content }}</p>
                    </div>
                @endforeach
            </div>
        </x-dashboard-card>

        <x-dashboard-card title="Demo requests" description="The latest people asking to see the homepage story in action.">
            @if ($recentDemoRequests->isEmpty())
                <x-empty-state
                    title="No demo requests yet"
                    description="When a visitor submits the public form, the request appears here."
                    primary-label="View live homepage"
                    primary-href="{{ route('home') }}"
                    tone="info"
                />
            @else
                <div class="space-y-3">
                    @foreach ($recentDemoRequests as $request)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-slate-950">{{ $request->name }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $request->email }}</p>
                                </div>
                                <x-status-badge :tone="$request->status === 'contacted' ? 'info' : ($request->status === 'closed' ? 'success' : 'warning')">
                                    {{ ucfirst($request->status) }}
                                </x-status-badge>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $request->organization ?? 'No organization provided' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-dashboard-card>
    </div>
</div>
@endsection
