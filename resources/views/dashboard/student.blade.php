@extends('layouts.dashboard')
@section('title', 'Student Dashboard')

@section('content')
<div class="space-y-8">
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.12),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(14,165,233,0.14),_transparent_24%),linear-gradient(135deg,_#ffffff_0%,_#f8fafc_55%,_#eff6ff_100%)] px-6 py-8 shadow-2xl shadow-slate-950/10 sm:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Learner workspace</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Hi, {{ $user->displayName() }}.</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    Join the protected Live Interactive Classroom, watch teacher corrections in real time, and keep your homework, quizzes, and projects inside one safe learning workspace.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('join') }}">
                    Join Live Class
                </x-primary-button>
                <x-secondary-button href="{{ route('live-interactive-classroom') }}">
                    Open classroom hub
                </x-secondary-button>
                <x-secondary-button href="{{ route('academic.my-homework') }}">
                    My Homework
                </x-secondary-button>
                <x-secondary-button href="{{ route('gamification.my-progress') }}">
                    My Progress
                </x-secondary-button>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Next sessions</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['upcoming_sessions'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Homework</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['homework_count'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Quizzes</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['quiz_count'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Points</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['points'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Badges</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['badges'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Certificates</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['certificates'] }}</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Next live session</h2>
                    <p class="text-sm text-slate-500">This is your main learning room.</p>
                </div>
                <a href="{{ route('join') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Join now</a>
            </div>

            @if ($nextSession)
                <div class="mt-5 rounded-[1.75rem] bg-[linear-gradient(135deg,_rgba(14,165,233,0.12),_rgba(34,197,94,0.08))] p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Protected classroom</p>
                            <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $nextSession->title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Teacher and student stay inside the same workspace while the teacher guides with the whiteboard, text pad, code editor, and pointer.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600">
                                <span class="rounded-full bg-white/80 px-3 py-1 font-semibold">{{ $nextSession->classe?->name ?? 'General classroom' }}</span>
                                <span class="rounded-full bg-white/80 px-3 py-1 font-semibold">{{ $nextSession->subject?->name ?? 'General session' }}</span>
                                <span class="rounded-full bg-white/80 px-3 py-1 font-semibold">{{ optional($nextSession->scheduled_at ?? $nextSession->created_at)->format('M j, g:i A') }}</span>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/70 bg-white px-5 py-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Current teacher / tutor</p>
                            <p class="mt-2 text-lg font-bold text-slate-900">{{ $currentTeacher?->displayName() ?? 'Teacher will appear here' }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $currentTeacher?->email ?? 'Connected teacher information will appear when the live session is ready.' }}</p>
                            <x-primary-button href="{{ route('join') }}" class="mt-4">
                                Join live class
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-5 py-6">
                    <p class="text-sm text-slate-600">No live session is scheduled yet.</p>
                    <p class="mt-1 text-sm text-slate-500">When your teacher opens a room, it will appear here so you can join immediately.</p>
                </div>
            @endif
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">My teacher / tutor</h2>
                    <p class="text-sm text-slate-500">The person guiding your learning.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Teacher</p>
                    <p class="mt-2 text-base font-bold text-slate-900">{{ $currentTeacher?->displayName() ?? 'No teacher linked yet' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Classes / groups</p>
                    <p class="mt-2 text-base font-bold text-slate-900">{{ $classes->count() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Learning points</p>
                    <p class="mt-2 text-base font-bold text-slate-900">{{ $learningPoints }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">My classes and groups</h2>
                    <p class="text-sm text-slate-500">These are the learning spaces where your teacher works with you.</p>
                </div>
                <a href="{{ route('live-interactive-classroom') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open hub</a>
            </div>

            @if ($classes->isNotEmpty())
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($classes as $class)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <div class="text-sm font-semibold text-slate-900">{{ $class->name }}</div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $class->teachers->map(fn ($teacher) => $teacher->displayName())->filter()->join(', ') ?: 'Teacher assigned' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                    You are not enrolled in a class yet.
                </div>
            @endif
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">My homework</h2>
                    <p class="text-sm text-slate-500">Tasks assigned by your teacher.</p>
                </div>
                <a href="{{ route('academic.my-homework') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open</a>
            </div>

            <div class="mt-5 rounded-2xl bg-emerald-50 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-500">Published homework</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $homeworkCount }}</p>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">My quizzes</h2>
                    <p class="text-sm text-slate-500">Practice, attempts, and results.</p>
                </div>
                <a href="{{ route('academic.quizzes.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open</a>
            </div>

            <div class="mt-5 rounded-2xl bg-amber-50 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">Assigned quizzes</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $quizCount }}</p>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($recentQuizAttempts as $attempt)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $attempt->quiz?->title ?? 'Quiz attempt' }}</div>
                        <div class="mt-1 text-xs text-slate-500">
                            Score: {{ $attempt->score ?? 'Pending' }}
                            @if ($attempt->submitted_at)
                                <span class="mx-1">•</span>{{ $attempt->submitted_at->format('M j, Y') }}
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No quiz attempts yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent student submissions</h2>
                    <p class="text-sm text-slate-500">Homework and coding work you can review.</p>
                </div>
                <a href="{{ route('coding.my-submissions') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open coding studio</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentProjects as $project)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $project->title }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $project->teacher?->displayName() ?? 'Teacher' }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No coding projects yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Badges & certificates</h2>
                    <p class="text-sm text-slate-500">Your wins and milestones.</p>
                </div>
                <a href="{{ route('gamification.my-progress') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Progress</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($badges as $badge)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $badge->badge?->name ?? 'Badge earned' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $badge->awarded_at?->format('M j, Y') ?? 'Recently' }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No badges yet.</p>
                @endforelse

                @forelse ($certificates as $certificate)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $certificate->title ?? $certificate->course_name ?? 'Certificate' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $certificate->issued_at?->format('M j, Y') ?? 'Recently' }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No certificates yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent feedback</h2>
                    <p class="text-sm text-slate-500">Helpful notes from your teacher or tutor.</p>
                </div>
                <a href="{{ route('academic.feedback.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Feedback</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentFeedback as $feedback)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $feedback->title ?? 'Teacher feedback' }}</div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $feedback->teacher?->displayName() ?? 'Teacher' }}
                            @if ($feedback->classe?->name)
                                <span class="mx-1">•</span>{{ $feedback->classe->name }}
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No feedback yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">My projects</h2>
                    <p class="text-sm text-slate-500">Recent coding work and personal projects.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentProjects as $project)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $project->title }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ ucfirst($project->status ?? 'draft') }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No projects yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Quick actions</h2>
                <p class="mt-1 text-sm text-slate-500">Everything leads back to your protected classroom and learning tools.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <x-quick-action-card href="{{ route('join') }}" tone="info" badge="Join" title="Join Live Class" description="Enter the room and start learning now." />
            <x-quick-action-card href="{{ route('live-interactive-classroom') }}" tone="neutral" badge="Workspace" title="Live Interactive Classroom" description="Open the protected lesson room preview." />
            <x-quick-action-card href="{{ route('academic.my-homework') }}" tone="success" badge="Work" title="My Homework" description="Review tasks from your teacher." />
            <x-quick-action-card href="{{ route('academic.quizzes.index') }}" tone="warning" badge="Quiz" title="My Quizzes" description="See the quizzes assigned to you." />
            <x-quick-action-card href="{{ route('gamification.my-progress') }}" tone="purple" badge="Progress" title="My Progress" description="Track badges, certificates, and milestones." />
        </div>
    </section>
</div>
@endsection
