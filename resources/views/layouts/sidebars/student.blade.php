<div class="space-y-6">
    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Learning</p>
        <x-sidebar-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            Dashboard
        </x-sidebar-link>
        <x-sidebar-link :href="route('join')" :active="request()->routeIs('join') || request()->routeIs('join.*')" icon="M12 4v16m8-8H4">
            Join Live Class
        </x-sidebar-link>
        <x-sidebar-link :href="route('classrooms.index')" :active="request()->routeIs('classrooms.*')" icon="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
            My Sessions
        </x-sidebar-link>
        <x-sidebar-link :href="route('live-interactive-classroom')" :active="request()->routeIs('live-interactive-classroom')" icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
            Whiteboard Activities
        </x-sidebar-link>
        <x-sidebar-link :href="route('coding.my-submissions')" :active="request()->routeIs('coding.my-submissions') || request()->routeIs('coding.workspace') || request()->routeIs('coding.sessions.*')" icon="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4">
            Coding Projects
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Assignments</p>
        <x-sidebar-link :href="route('academic.my-homework')" :active="request()->routeIs('academic.my-homework') || request()->routeIs('academic.homeworks.submit')" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
            Homework
        </x-sidebar-link>
        <x-sidebar-link :href="route('student.quizzes')" :active="request()->routeIs('student.quizzes')" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
            Quizzes
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Progress</p>
        <x-sidebar-link :href="route('student.reports')" :active="request()->routeIs('student.reports') || request()->routeIs('academic.reports.*')" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
            Reports
        </x-sidebar-link>
        <x-sidebar-link :href="route('gamification.my-progress')" :active="request()->routeIs('gamification.my-progress')" icon="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
            Badges
        </x-sidebar-link>
        <x-sidebar-link :href="route('student.certificates')" :active="request()->routeIs('student.certificates')" icon="M9 12a3 3 0 116 0 3 3 0 01-6 0zm0 0v8l3-2 3 2v-8">
            Certificates
        </x-sidebar-link>
    </div>
</div>
