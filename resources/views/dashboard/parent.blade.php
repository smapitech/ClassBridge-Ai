@extends('layouts.dashboard')
@section('title', 'Parent Dashboard')

@section('content')
<div class="space-y-8">
    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.12),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(168,85,247,0.08),_transparent_24%),linear-gradient(135deg,_#ffffff_0%,_#f8fafc_52%,_#eef2ff_100%)] px-6 py-8 shadow-2xl shadow-slate-950/10 sm:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Parent workspace</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Track your child&apos;s progress without entering the private device.</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                    See live sessions, homework, quiz scores, reports, replays, and teacher feedback from the protected ClassBridge AI workspace.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button href="{{ route('academic.reports.index') }}">
                    Open reports
                </x-primary-button>
                <x-secondary-button href="{{ route('live-interactive-classroom') }}">
                    Live Interactive Classroom
                </x-secondary-button>
                <x-secondary-button href="{{ route('lesson-replays.index') }}">
                    Lesson replays
                </x-secondary-button>
                <x-status-badge tone="warning">
                    Message teacher/tutor coming soon
                </x-status-badge>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-4 lg:grid-cols-4 xl:grid-cols-8">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Linked children</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['linked_children'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Upcoming sessions</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['upcoming_sessions'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Homework status</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['homework_items'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Quiz scores</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['quiz_scores'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Feedback</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['feedback_items'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Reports</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['reports'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Achievements</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['achievements'] }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Lesson replays</p>
            <p class="mt-3 text-3xl font-black text-slate-900">{{ $stats['lesson_replays'] }}</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Linked children</h2>
                    <p class="text-sm text-slate-500">Each learner is connected to your parent account.</p>
                </div>
                <a href="{{ route('academic.attendance.parent') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Attendance</a>
            </div>

            @if ($childSummaries->isNotEmpty())
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($childSummaries as $summary)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900">{{ $summary['name'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $summary['email'] }}</div>
                                    <div class="mt-2 text-xs text-slate-500">{{ $summary['school'] ?? 'School / organization' }}</div>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-slate-600">{{ $summary['class_count'] }} groups</span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-600">
                                <div class="rounded-2xl bg-white px-3 py-2">Homework: {{ $summary['homework_count'] }}</div>
                                <div class="rounded-2xl bg-white px-3 py-2">Quizzes: {{ $summary['quiz_count'] }}</div>
                                <div class="rounded-2xl bg-white px-3 py-2">Feedback: {{ $summary['feedback_count'] }}</div>
                                <div class="rounded-2xl bg-white px-3 py-2">Certificates: {{ $summary['certificate_count'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                    No children are linked to this parent account yet.
                </div>
            @endif
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Upcoming live sessions</h2>
                    <p class="text-sm text-slate-500">Watch when your child is scheduled to learn.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($upcomingSessions as $session)
                    <div class="rounded-2xl bg-sky-50 px-4 py-4">
                        <div class="text-sm font-semibold text-slate-900">{{ $session->title }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $session->teacher?->displayName() ?? 'Teacher / tutor' }}</div>
                        <div class="mt-2 text-xs text-slate-500">{{ optional($session->scheduled_at ?? $session->created_at)->format('M j, g:i A') }}</div>
                    </div>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">No upcoming live sessions yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Homework status</h2>
                    <p class="text-sm text-slate-500">See the learning tasks your child is working on.</p>
                </div>
                <a href="{{ route('academic.my-homework') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Homework</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($childSummaries as $summary)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $summary['name'] }}</div>
                        <div class="mt-1 text-xs text-slate-500">Homework assignments: {{ $summary['homework_count'] }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Homework will appear once children are linked.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Quiz scores</h2>
                    <p class="text-sm text-slate-500">Latest attempts and outcomes.</p>
                </div>
                <a href="{{ route('academic.quizzes.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Quizzes</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($quizAttempts as $attempt)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $attempt->student?->displayName() ?? 'Child' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $attempt->quiz?->title ?? 'Quiz attempt' }}</div>
                        <div class="mt-2 text-xs text-slate-500">
                            Score: {{ $attempt->score ?? 'Pending' }}
                            @if ($attempt->submitted_at)
                                <span class="mx-1">•</span>{{ $attempt->submitted_at->format('M j, Y') }}
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No quiz scores yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Progress reports</h2>
                    <p class="text-sm text-slate-500">Published reports that summarize learning progress.</p>
                </div>
                <a href="{{ route('academic.reports.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Reports</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($publishedReports as $report)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $report->student?->displayName() ?? 'Child' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $report->created_at->format('M j, Y') }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No published reports yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Teacher / tutor feedback</h2>
                    <p class="text-sm text-slate-500">Quick notes from the classroom team.</p>
                </div>
                <a href="{{ route('academic.feedback.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Feedback</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($teacherFeedback as $feedback)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $feedback->title ?? 'Teacher feedback' }}</div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $feedback->teacher?->displayName() ?? 'Teacher / tutor' }}
                            @if ($feedback->student?->displayName())
                                <span class="mx-1">•</span>{{ $feedback->student->displayName() }}
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
                    <h2 class="text-lg font-bold text-slate-900">Lesson replay</h2>
                    <p class="text-sm text-slate-500">Replay the session later for revision.</p>
                </div>
                <a href="{{ route('lesson-replays.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Replays</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($lessonReplays as $replay)
                    <div class="rounded-2xl border border-slate-100 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-900">{{ $replay->title }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $replay->created_at->format('M j, Y') }}</div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                        Lesson replays will appear here when the teacher enables them.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Child achievements</h2>
                <p class="mt-1 text-sm text-slate-500">Badges, certificates, and milestones earned by your children.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($achievements as $achievement)
                <div class="rounded-2xl bg-amber-50 px-4 py-4">
                    <div class="text-sm font-semibold text-slate-900">{{ $achievement->title }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ $achievement->student?->displayName() ?? 'Child' }}</div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                    Achievements will appear here once the classroom begins awarding them.
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Quick actions</h2>
                <p class="mt-1 text-sm text-slate-500">Keep an eye on progress, reports, and classroom activity.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <x-quick-action-card href="{{ route('academic.attendance.parent') }}" tone="info" badge="Attend" title="Attendance" description="Review lesson attendance at a glance." />
            <x-quick-action-card href="{{ route('academic.reports.index') }}" tone="success" badge="Reports" title="Progress reports" description="Open summaries shared by the teacher or tutor." />
            <x-quick-action-card href="{{ route('academic.feedback.index') }}" tone="warning" badge="Notes" title="Teacher feedback" description="See guidance and classroom notes." />
            <x-quick-action-card href="{{ route('live-interactive-classroom') }}" tone="neutral" badge="Live" title="Live Interactive Classroom" description="Open the protected learning workspace preview." />
            <x-quick-action-card href="{{ route('lesson-replays.index') }}" tone="purple" badge="Replay" title="Lesson replays" description="Review the lesson after it finishes." />
        </div>
    </section>
</div>
@endsection
