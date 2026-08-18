<div class="space-y-6">
    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Family</p>
        <x-sidebar-link :href="route('parent.dashboard')" :active="request()->routeIs('parent.dashboard')" icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
            Dashboard
        </x-sidebar-link>
        <x-sidebar-link :href="route('parent.children')" :active="request()->routeIs('parent.children')" icon="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
            My Children
        </x-sidebar-link>
        <x-sidebar-link :href="route('classrooms.index')" :active="request()->routeIs('classrooms.*')" icon="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
            Live Session Schedule
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Learning</p>
        <x-sidebar-link :href="route('parent.homework')" :active="request()->routeIs('parent.homework') || request()->routeIs('academic.homeworks.*')" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
            Homework
        </x-sidebar-link>
        <x-sidebar-link :href="route('parent.quizzes')" :active="request()->routeIs('parent.quizzes') || request()->routeIs('academic.quizzes.*')" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
            Quiz Scores
        </x-sidebar-link>
        <x-sidebar-link :href="route('parent.progress')" :active="request()->routeIs('parent.progress') || request()->routeIs('academic.reports.*')" icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
            Progress Reports
        </x-sidebar-link>
        <x-sidebar-link :href="route('lesson-replays.index')" :active="request()->routeIs('lesson-replays.*')" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
            Lesson Replay
        </x-sidebar-link>
    </div>

    <div class="space-y-1">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Engagement</p>
        <x-sidebar-link :href="route('parent.achievements')" :active="request()->routeIs('parent.achievements')" icon="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
            Achievements
        </x-sidebar-link>
        <x-sidebar-link :href="route('parent.messages')" :active="request()->routeIs('parent.messages') || request()->routeIs('academic.feedback.*')" icon="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
            Messages
        </x-sidebar-link>
        <x-sidebar-link :href="route('parent.payments')" :active="request()->routeIs('parent.payments') || request()->routeIs('billing.*')" icon="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
            Payments
        </x-sidebar-link>
    </div>
</div>
