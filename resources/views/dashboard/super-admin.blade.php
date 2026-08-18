@extends('layouts.dashboard')
@section('title', 'Platform Dashboard')

@section('content')
<div class="space-y-8">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-900/10 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(99,102,241,0.16),_transparent_24%),linear-gradient(135deg,_#020617_0%,_#0f172a_48%,_#111827_100%)] px-6 py-8 text-white shadow-2xl shadow-slate-950/20 sm:px-8">
        <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-[radial-gradient(circle,_rgba(255,255,255,0.18),_transparent_70%)] lg:block"></div>
        <div class="relative grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200">Platform command center</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-5xl">Live classroom is the heartbeat of every organization on ClassBridge AI.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                    Schools, tutoring centers, coding academies, private tutors, homeschool tutors, and online lesson businesses all meet inside one protected learning workspace where live teaching happens in real time.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-primary-button href="{{ route('super-admin.web-builder.index') }}" variant="light">
                        Open web builder
                    </x-primary-button>
                    <x-primary-button href="{{ route('super-admin.schools.index') }}" variant="light">
                        Manage organizations
                    </x-primary-button>
                    <x-secondary-button href="{{ route('classrooms.index') }}" variant="inverse">
                        View live sessions
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('ai.admin.providers.index') }}" variant="inverse">
                        Manage AI providers
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('billing.admin.plans') }}" variant="inverse">
                        Manage subscription plans
                    </x-secondary-button>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-white/10 bg-white/10 p-5 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-slate-300">Platform pulse</p>
                        <p class="mt-1 text-xl font-black">Live teaching health</p>
                    </div>
                    <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-[11px] font-semibold text-emerald-200">Protected workspace</span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Active sessions</p>
                        <p class="mt-2 text-3xl font-black">{{ $stats['active_live_sessions'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-300">AI usage</p>
                        <p class="mt-2 text-3xl font-black">{{ $stats['ai_usage_this_month'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Organizations</p>
                        <p class="mt-2 text-3xl font-black">{{ $stats['total_organizations'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-slate-300">Tutors</p>
                        <p class="mt-2 text-3xl font-black">{{ $stats['total_teachers_tutors'] }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">The product message</p>
                    <p class="mt-2 text-sm leading-6 text-slate-200">
                        Teach online like you are sitting beside the child without remote desktop access risk.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-5">
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total organizations</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['total_organizations'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Schools</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['schools_count'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Private tutors</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['private_tutors_count'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Tutoring centers</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['tutoring_centers_count'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Active live sessions</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['active_live_sessions'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Teachers / Tutors</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['total_teachers_tutors'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Students / Learners</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['total_students_learners'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Parents</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['total_parents'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">AI usage</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['ai_usage_this_month'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $stats['ai_usage_total'] }} total generations</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Subscriptions</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['active_organization_subscriptions'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $stats['subscription_plans'] }} plans available</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 overflow-hidden rounded-[2rem] border border-slate-200 bg-white/95 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent organizations</h2>
                    <p class="text-sm text-slate-500">The platform now supports schools and tutors under one workspace model.</p>
                </div>
                <a href="{{ route('super-admin.schools.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Manage all</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Owner</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($recentOrganizations as $organization)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $organization->displayLabel() }}</div>
                                    <div class="text-xs text-slate-500">{{ $organization->slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ classbridge_organization_type_label($organization->organization_type) }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $organization->owner?->displayName() ?? 'Unassigned' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $organization->users_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                        {{ $organization->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($organization->status === 'trial' ? 'bg-sky-50 text-sky-700' : ($organization->status === 'suspended' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600')) }}">
                                        {{ ucfirst($organization->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No organizations have been created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">Organization breakdown</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Live mix</span>
                </div>

                <div class="mt-5 space-y-3">
                    @foreach ($organizationBreakdown as $item)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="text-sm font-semibold text-slate-700">{{ $item['label'] }}</span>
                            <span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-slate-900">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">AI summary</h2>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-violet-700">Usage</span>
                </div>

                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">This month</span>
                        <span class="font-semibold text-slate-900">{{ $aiSummary['this_month'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Total generations</span>
                        <span class="font-semibold text-slate-900">{{ $aiSummary['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Providers</span>
                        <span class="font-semibold text-slate-900">{{ $aiSummary['providers'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent live sessions</h2>
                    <p class="text-sm text-slate-500">The product is positioned around these protected rooms first.</p>
                </div>
                <a href="{{ route('classrooms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open hub</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentLiveSessions as $session)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $session->title }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $session->school?->displayLabel() ?? 'Organization' }}
                                    @if ($session->teacher)
                                        <span class="mx-1">•</span>{{ $session->teacher->displayName() }}
                                    @endif
                                </p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold
                                {{ $session->status === 'live' ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            {{ $session->classe?->name ?? 'General classroom' }}
                            @if ($session->subject?->name)
                                <span class="mx-1">•</span>{{ $session->subject->name }}
                            @endif
                            <span class="mx-1">•</span>{{ optional($session->scheduled_at ?? $session->created_at)->format('M j, g:i A') }}
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                        No live sessions yet. Once teachers start teaching, the active rooms will appear here.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Subscription summary</h2>
                    <p class="text-sm text-slate-500">Keep the platform healthy for schools, tutors, and academies.</p>
                </div>
                <a href="{{ route('billing.admin.subscriptions') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Subscriptions</a>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Plans</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $subscriptionSummary['plans'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Active plans</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $subscriptionSummary['active_plans'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Active subscriptions</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $subscriptionSummary['active_subscriptions'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Trial subscriptions</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $subscriptionSummary['trial_subscriptions'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Quick actions</h2>
                <p class="mt-1 text-sm text-slate-500">Put the live classroom at the center of platform administration.</p>
            </div>
        <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('super-admin.web-builder.index') }}">
                    Open web builder
                </x-primary-button>
                <x-primary-button href="{{ route('super-admin.schools.create') }}">
                    Manage organizations
                </x-primary-button>
                <x-secondary-button href="{{ route('classrooms.index') }}">
                    View live sessions
                </x-secondary-button>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-quick-action-card
                href="{{ route('super-admin.web-builder.index') }}"
                tone="info"
                badge="Web"
                title="Open web builder"
                description="Edit the public homepage, hero copy, and request form."
            />
            <x-quick-action-card
                href="{{ route('super-admin.schools.index') }}"
                tone="info"
                badge="Global"
                title="Manage organizations"
                description="Add schools, tutoring centers, and private workspaces."
            />
            <x-quick-action-card
                href="{{ route('ai.admin.providers.index') }}"
                tone="purple"
                badge="AI"
                title="Manage AI providers"
                description="Control the models behind lesson generation and support."
            />
            <x-quick-action-card
                href="{{ route('classrooms.index') }}"
                tone="success"
                badge="Live"
                title="View live sessions"
                description="Open the protected rooms where teaching is happening now."
            />
            <x-quick-action-card
                href="{{ route('billing.admin.plans') }}"
                tone="warning"
                badge="Billing"
                title="Manage subscription plans"
                description="Tune the plans that power school and tutor accounts."
            />
        </div>
    </section>
</div>
@endsection
