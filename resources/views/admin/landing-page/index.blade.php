@extends('layouts.dashboard')

@section('title', 'Landing Page')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Landing Page"
        description="Edit the public homepage from the dashboard. Keep the copy short, warm, and easy for parents, tutors, and schools to understand."
    >
        <x-slot:actions>
            <x-primary-button href="{{ route('super-admin.landing-page.slides.create') }}">Add slide</x-primary-button>
            <x-secondary-button href="{{ route('home') }}" target="_blank">View live page</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([
            ['Slides', $stats['slides'], 'info'],
            ['Features', $stats['features'], 'success'],
            ['Audiences', $stats['audiences'], 'purple'],
            ['Pricing items', $stats['pricing'], 'warning'],
            ['Sections', $stats['sections'], 'neutral'],
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
        <x-dashboard-card title="Hero slides" description="These slides drive the first message on the landing page.">
            @if ($slides->isEmpty())
                <x-empty-state
                    title="No slides yet"
                    description="Create a few short hero slides so the homepage can rotate through the main message."
                    primary-label="Add first slide"
                    primary-href="{{ route('super-admin.landing-page.slides.create') }}"
                    tone="info"
                />
            @else
                <div class="space-y-3">
                    @foreach ($slides->take(4) as $slide)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ $slide->label ?? 'Slide' }}</p>
                                    <h3 class="mt-1 text-base font-black text-slate-950">{{ $slide->headline }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $slide->subtitle }}</p>
                                </div>
                                <x-status-badge :tone="$slide->is_active ? 'success' : 'neutral'">{{ $slide->is_active ? 'Active' : 'Hidden' }}</x-status-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-dashboard-card>

        <x-dashboard-card title="Quick actions" description="Jump into the parts that shape the public story.">
            <div class="grid gap-3 sm:grid-cols-2">
                <x-quick-action-card href="{{ route('super-admin.landing-page.slides.index') }}" tone="info" badge="Hero" title="Manage hero slides" description="Write the top message in short, human copy." />
                <x-quick-action-card href="{{ route('super-admin.landing-page.features.index') }}" tone="success" badge="Core" title="Manage features" description="Keep the product value clear and practical." />
                <x-quick-action-card href="{{ route('super-admin.landing-page.audiences.index') }}" tone="purple" badge="People" title="Manage audiences" description="Shape the cards for schools, tutors, and parents." />
                <x-quick-action-card href="{{ route('super-admin.landing-page.pricing.index') }}" tone="warning" badge="Plans" title="Manage pricing preview" description="Keep the plans simple and easy to compare." />
                <x-quick-action-card href="{{ route('super-admin.landing-page.sections') }}" tone="neutral" badge="Sections" title="Edit page sections" description="Tune the story blocks shown on the landing page." />
                <x-quick-action-card href="{{ route('super-admin.demo-requests.index') }}" tone="danger" badge="Leads" title="Review demo requests" description="See who wants a guided walkthrough." />
            </div>
        </x-dashboard-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <x-dashboard-card title="Recent live content" description="A quick view of the public content that is already active.">
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($sections->take(6) as $section)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ $section->section_key }}</p>
                                <h3 class="mt-1 font-black text-slate-950">{{ $section->title ?? 'Untitled section' }}</h3>
                            </div>
                            <x-status-badge :tone="$section->is_active ? 'success' : 'neutral'">{{ $section->is_active ? 'Live' : 'Hidden' }}</x-status-badge>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $section->subtitle ?? $section->content }}</p>
                    </div>
                @endforeach
            </div>
        </x-dashboard-card>

        <x-dashboard-card title="Recent demo requests" description="Latest people asking for a walkthrough.">
            @if ($recentDemoRequests->isEmpty())
                <x-empty-state
                    title="No demo requests yet"
                    description="When people submit the form, their requests will appear here."
                    primary-label="View landing page"
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
