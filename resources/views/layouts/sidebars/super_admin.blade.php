<div class="space-y-6">
    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Platform</p>
        <x-sidebar-link :href="route('super-admin.dashboard')" :active="request()->routeIs('super-admin.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            Dashboard
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.organizations.index')" :active="request()->routeIs('super-admin.organizations.*')" icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
            Organizations
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.schools.index')" :active="request()->routeIs('super-admin.schools.*')" icon="M5 3v16h14V3H5zm2 2h10v12H7V5zm2 2v2h6V7H9zm0 4v2h6v-2H9z">
            Schools
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.tutors.index')" :active="request()->routeIs('super-admin.tutors.*')" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z">
            Tutors
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.users.index')" :active="request()->routeIs('super-admin.users.*')" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z">
            Users
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Web Builder</p>
        <x-sidebar-link :href="route('super-admin.web-builder.index')" :active="request()->routeIs('super-admin.web-builder.index')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            Overview
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.web-builder.slides.index')" :active="request()->routeIs('super-admin.web-builder.slides.*')" icon="M4 6h16M4 12h16M4 18h16">
            Hero Slides
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.web-builder.features.index')" :active="request()->routeIs('super-admin.web-builder.features.*')" icon="M13 10V3L4 14h7v7l9-11h-7z">
            Features
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.web-builder.audiences.index')" :active="request()->routeIs('super-admin.web-builder.audiences.*')" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z">
            Audiences
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.web-builder.pricing.index')" :active="request()->routeIs('super-admin.web-builder.pricing.*')" icon="M12 8c-2.28 0-4 .79-4 2s1.72 2 4 2 4 .79 4 2-1.72 2-4 2-4-.79-4-2m4-6V5m0 14v-3m8-4a8 8 0 11-16 0 8 8 0 0116 0z">
            Pricing Preview
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.web-builder.sections')" :active="request()->routeIs('super-admin.web-builder.sections*')" icon="M4 6h16M4 12h16M4 18h16">
            Page Blocks
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.web-builder.demo-requests.index')" :active="request()->routeIs('super-admin.web-builder.demo-requests.*')" icon="M9 12h6m-3-3v6m7 2a9 9 0 11-18 0 9 9 0 0118 0z">
            Demo Requests
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Teaching</p>
        <x-sidebar-link :href="route('classrooms.index')" :active="request()->routeIs('classrooms.*')" icon="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
            Live Sessions
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">AI</p>
        <x-sidebar-link :href="route('ai.admin.providers.index')" :active="request()->routeIs('ai.admin.providers.*')" icon="M13 10V3L4 14h7v7l9-11h-7z">
            AI Providers
        </x-sidebar-link>
        <x-sidebar-link :href="route('ai.admin.usage')" :active="request()->routeIs('ai.admin.usage')" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
            AI Usage
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Billing</p>
        <x-sidebar-link :href="route('billing.admin.subscriptions')" :active="request()->routeIs('billing.admin.subscriptions*')" icon="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
            Subscriptions
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Security</p>
        <x-sidebar-link :href="route('super-admin.audit-logs')" :active="request()->routeIs('super-admin.audit-logs')" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
            Audit Logs
        </x-sidebar-link>
        <x-sidebar-link :href="route('super-admin.settings')" :active="request()->routeIs('super-admin.settings')" icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z">
            Settings
        </x-sidebar-link>
    </div>
</div>
