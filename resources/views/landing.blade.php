@extends('layouts.public')

@section('title', 'Live Interactive Teaching Platform')

@section('content')
@php
    $liveModes = [
        ['name' => 'Whiteboard Mode', 'copy' => 'Sketch, annotate, and explain visually.'],
        ['name' => 'Coding Mode', 'copy' => 'Teach code with a shared editor and live guidance.'],
        ['name' => 'Text / English Mode', 'copy' => 'Read, write, and correct together in real time.'],
        ['name' => 'Math Mode', 'copy' => 'Work through equations step by step.'],
        ['name' => 'Presentation Mode', 'copy' => 'Use slides and guided teaching flow.'],
    ];

    $audiences = [
        ['title' => 'Schools', 'copy' => 'Keep teachers, classes, students, and parents in one protected organization workspace.'],
        ['title' => 'Private tutors', 'copy' => 'Run a solo teaching business without creating a full school structure first.'],
        ['title' => 'Online tutors', 'copy' => 'Teach remotely with live pointers, chat, and guided corrections.'],
        ['title' => 'Coding academies', 'copy' => 'Use the shared coding studio to teach languages and projects live.'],
        ['title' => 'Homeschool teachers', 'copy' => 'Support one child or a family with a private online classroom.'],
        ['title' => 'After-school lesson teachers', 'copy' => 'Offer focused support in a safe workspace after class hours.'],
    ];

    $workflow = [
        ['step' => '01', 'title' => 'Teacher creates session', 'copy' => 'Launch a live classroom, choose the teaching mode, and share the room link or code.'],
        ['step' => '02', 'title' => 'Student joins by link or code', 'copy' => 'The learner enters the protected workspace with no access to the rest of the device.'],
        ['step' => '03', 'title' => 'Both work inside the same classroom', 'copy' => 'Teacher and student can write, draw, code, and explain together in real time.'],
        ['step' => '04', 'title' => 'Teacher guides with live tools', 'copy' => 'Use the pointer, whiteboard, text pad, chat, and code editor to teach beside the child.'],
        ['step' => '05', 'title' => 'Parent can receive reports', 'copy' => 'Share lesson summaries, progress reports, and replay links later.'],
        ['step' => '06', 'title' => 'No remote desktop access is required', 'copy' => "All interaction stays inside ClassBridge AI, not the student's personal computer."],
    ];

    $features = [
        ['title' => 'Live Interactive Classroom', 'copy' => 'The central teaching space for every live lesson.'],
        ['title' => 'Shared Whiteboard', 'copy' => 'Draw, annotate, and solve together on the same canvas.'],
        ['title' => 'Shared Coding Studio', 'copy' => 'Code side by side with visible live edits.'],
        ['title' => 'Teacher and Student Pointer', 'copy' => 'Both cursors remain visible in the protected workspace.'],
        ['title' => 'Shared Text Pad', 'copy' => 'Write, correct, and review text live.'],
        ['title' => 'AI Lesson Builder', 'copy' => 'Generate lesson plans that support your teaching flow.'],
        ['title' => 'AI Curriculum Generator', 'copy' => 'Plan a wider learning journey across topics.'],
        ['title' => 'Homework and Quiz Tools', 'copy' => 'Set practice work and review understanding.'],
        ['title' => 'Parent Progress Portal', 'copy' => 'Keep parents informed without opening private devices.'],
        ['title' => 'Smart Lesson Replay', 'copy' => 'Review what happened in the session later.'],
        ['title' => 'Certificates', 'copy' => 'Recognize progress and completion.'],
        ['title' => 'Tutor and School Accounts', 'copy' => 'Support both private teaching and larger organizations.'],
    ];

    $plans = [
        [
            'name' => 'Private Tutor',
            'summary' => 'For one tutor running a private online teaching business.',
            'items' => ['Add students', 'Schedule sessions', 'Teach live', 'Send homework'],
            'tone' => 'from-sky-50 to-white',
        ],
        [
            'name' => 'Small Tutoring Team',
            'summary' => 'For growing tutors with a few teachers and shared learners.',
            'items' => ['Shared classroom tools', 'Parent reporting', 'Lesson replay', 'AI lesson support'],
            'tone' => 'from-indigo-50 to-white',
            'featured' => true,
        ],
        [
            'name' => 'School / Academy',
            'summary' => 'For schools, centers, and academies with more structure.',
            'items' => ['Teachers and classes', 'Students and parents', 'Subscriptions', 'Reports'],
            'tone' => 'from-emerald-50 to-white',
        ],
        [
            'name' => 'Enterprise',
            'summary' => 'For larger organizations that need custom rollout and support.',
            'items' => ['Multiple workspaces', 'Advanced governance', 'Training support', 'Custom implementation'],
            'tone' => 'from-slate-100 to-white',
        ],
    ];

    $tutorBenefits = [
        'Add students',
        'Schedule sessions',
        'Teach live',
        'Send homework',
        'Generate reports',
        'Share progress with parents',
        'Collect payments later',
    ];

    $schoolBenefits = [
        'Manage teachers',
        'Manage classes',
        'Manage students',
        'Link parents',
        'Run live sessions',
        'Review reports',
        'Manage subscriptions',
    ];
@endphp

<div class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 -z-10 h-[48rem] bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.20),_transparent_32%),radial-gradient(circle_at_top_right,_rgba(99,102,241,0.18),_transparent_28%),linear-gradient(180deg,_rgba(248,250,252,1)_0%,_rgba(239,246,255,0.9)_48%,_rgba(248,250,252,1)_100%)]"></div>
    <div class="absolute left-0 top-20 -z-10 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
    <div class="absolute right-0 top-96 -z-10 h-80 w-80 rounded-full bg-indigo-300/20 blur-3xl"></div>

    <section class="mx-auto max-w-7xl px-6 pb-14 pt-16 sm:pb-20 sm:pt-20 lg:px-8 lg:pt-24">
        <div class="grid gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
            <div class="max-w-3xl">
                <x-status-badge tone="info">
                    Protected live teaching workspace
                </x-status-badge>
                <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                    Teach online like you are sitting beside the child - without remote access risk.
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                    ClassBridge AI gives schools, tutors, and online teachers a protected live classroom where teacher and student can write, draw, code, point, explain, and learn together in real time.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-primary-button href="{{ route('register') }}">
                        Start Free Trial
                    </x-primary-button>
                    <x-secondary-button href="{{ route('demo.live-classroom') }}">
                        Try Demo Classroom
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('home') }}#request-demo">
                        Request Demo
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('login') }}">
                        Login
                    </x-secondary-button>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <span class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-medium text-slate-600">Whiteboard + coding + text pad</span>
                    <span class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-medium text-slate-600">Teacher and student pointers</span>
                    <span class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-medium text-slate-600">Chat + participant list</span>
                    <span class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-medium text-slate-600">No remote desktop access</span>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -left-8 -top-10 h-28 w-28 rounded-full bg-sky-300/25 blur-3xl"></div>
                <div class="absolute -right-8 top-28 h-36 w-36 rounded-full bg-indigo-300/20 blur-3xl"></div>

                <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/95 shadow-2xl shadow-slate-950/10 backdrop-blur">
                    <div class="border-b border-slate-200 bg-slate-950 px-5 py-4 text-white">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-slate-400">Live interactive classroom</p>
                                <p class="mt-1 text-lg font-bold">Teacher and learner are online together</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">Live</span>
                                <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">Room CB-2147</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-sky-50 via-white to-indigo-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="rounded-full bg-sky-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Teacher pointer</span>
                                <span class="rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Student pointer</span>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-white bg-white/95 shadow-sm">
                                <div class="relative h-60 bg-[linear-gradient(135deg,_rgba(15,23,42,1)_0%,_rgba(30,41,59,1)_100%)] p-4 text-white">
                                    <div class="absolute left-5 top-5 rounded-full bg-sky-500 px-3 py-1 text-[11px] font-semibold">Live whiteboard</div>
                                    <div class="absolute right-5 top-16 rounded-full bg-emerald-500 px-3 py-1 text-[11px] font-semibold">Student cursor</div>
                                    <div class="absolute bottom-5 left-5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold text-white/90">Teacher correction</div>

                                    <div class="absolute left-6 top-24 h-2 w-44 rounded-full bg-white/75"></div>
                                    <div class="absolute left-6 top-32 h-2 w-64 rounded-full bg-white/55"></div>
                                    <div class="absolute left-6 top-40 h-2 w-36 rounded-full bg-white/65"></div>

                                    <div class="absolute right-7 top-28 rounded-2xl border border-sky-200/40 bg-sky-400/10 px-4 py-3 text-sm text-sky-100">Teacher draws a quick example</div>
                                    <div class="absolute right-10 bottom-8 rounded-2xl border border-emerald-200/40 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">Student answers in real time</div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Shared text pad</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">See typing as it happens while the teacher edits with the learner.</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Shared code editor</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">Guide coding without touching the child's device.</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Live preview</p>
                                    <span class="rounded-full bg-indigo-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Coding</span>
                                </div>
                                <div class="mt-4 rounded-2xl bg-slate-950 p-4 text-left text-white">
                                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        Live code editor
                                    </div>
                                    <pre class="mt-4 overflow-x-auto text-sm leading-7 text-slate-100"><code>&lt;h1&gt;Today&apos;s lesson&lt;/h1&gt;
&lt;p&gt;Teacher edits with the learner.&lt;/p&gt;
&lt;button&gt;Run preview&lt;/button&gt;</code></pre>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Participant list</p>
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">Teacher</p>
                                            <p class="text-sm text-slate-500">Guiding the live session</p>
                                        </div>
                                        <span class="h-3 w-3 rounded-full bg-sky-500"></span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">Student</p>
                                            <p class="text-sm text-slate-500">Writing in the protected workspace</p>
                                        </div>
                                        <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Chat</p>
                                <div class="mt-4 space-y-3">
                                    <div class="rounded-2xl bg-sky-50 px-4 py-3 text-sm text-slate-700">
                                        Teacher: Let&apos;s fix this step together.
                                    </div>
                                    <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-slate-700">
                                        Student: I can see the correction now.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Live classroom preview</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">A premium classroom layout built around shared teaching.</h2>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        The classroom keeps teacher and student inside the same protected space so they can draw, type, code, explain, and correct together instantly.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($liveModes as $mode)
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600">
                            {{ $mode['name'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
                <div class="space-y-6">
                    <div class="rounded-[1.75rem] border border-slate-200 bg-slate-950 p-5 text-white shadow-lg shadow-slate-950/10">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Teacher and student are inside the same workspace</p>
                                <p class="mt-2 text-lg font-bold">Whiteboard, code, text, pointer, and chat stay in sync.</p>
                            </div>
                            <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">Session status: Live</span>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                            <div class="rounded-3xl border border-white/10 bg-[linear-gradient(135deg,_rgba(15,118,110,0.12),_rgba(255,255,255,0.04))] p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <span class="rounded-full bg-sky-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Teacher pointer visible</span>
                                    <span class="rounded-full bg-emerald-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Student pointer visible</span>
                                </div>

                                <div class="relative mt-4 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white p-4 text-slate-900">
                                    <div class="absolute left-5 top-5 rounded-full bg-sky-600 px-3 py-1 text-[11px] font-semibold text-white shadow-lg shadow-sky-900/15">Teacher correction</div>
                                    <div class="absolute right-5 top-16 rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white shadow-lg shadow-emerald-900/15">Student answer</div>

                                    <div class="grid h-64 grid-cols-[1.1fr_0.9fr] gap-3 pt-12">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                                <span>Shared whiteboard</span>
                                                <span>Math mode</span>
                                            </div>
                                            <div class="mt-4 space-y-3">
                                                <div class="h-2 w-5/6 rounded-full bg-slate-200"></div>
                                                <div class="h-2 w-3/4 rounded-full bg-slate-200"></div>
                                                <div class="h-2 w-2/3 rounded-full bg-slate-200"></div>
                                                <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-slate-700">
                                                    Solve together, line by line.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-slate-200 bg-slate-950 p-4 text-white">
                                            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                                <span>Shared code editor</span>
                                                <span>Live preview</span>
                                            </div>
                                            <pre class="mt-4 overflow-x-auto text-[12px] leading-6 text-slate-100"><code>const answer = 42;
function checkWork() {
  return answer.toString();
}</code></pre>
                                            <div class="mt-4 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200">
                                                The learner sees edits as they happen.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Session status</p>
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Active</span>
                                    </div>
                                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                                            <span>Join code</span>
                                            <span class="font-mono font-semibold text-slate-900">CB-2147</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                                            <span>Mode</span>
                                            <span class="font-semibold text-slate-900">Coding Mode</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                                            <span>Permissions</span>
                                            <span class="font-semibold text-slate-900">Shared typing enabled</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Teacher controls</p>
                                    <div class="mt-4 grid gap-2">
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">Switch classroom mode</div>
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">Allow or restrict student typing</div>
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">Save the session snapshot</div>
                                    </div>
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Protected note</p>
                                    <p class="mt-3 text-sm leading-6 text-slate-600">
                                        Teachers cannot access the student's computer, files, browser history, or other applications.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Shared text pad</p>
                            <p class="mt-3 text-sm leading-6 text-slate-600">Perfect for English, essay writing, proofreading, and live correction without leaving the protected classroom.</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Parent visibility</p>
                            <p class="mt-3 text-sm leading-6 text-slate-600">Progress, reports, and lesson replay can be shared later with parents or organization admins.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Participant list</p>
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">Teacher</p>
                                    <p class="text-sm text-slate-500">Guiding the lesson</p>
                                </div>
                                <span class="h-3 w-3 rounded-full bg-sky-500"></span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">Student</p>
                                    <p class="text-sm text-slate-500">Typing inside the workspace</p>
                                </div>
                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Chat</p>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-2xl bg-sky-50 px-4 py-3 text-sm text-slate-700">
                                Teacher: Let&apos;s correct this step together.
                            </div>
                            <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-slate-700">
                                Student: I can see the pointer move now.
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                Parent reports can be shared after the session.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Teaching modes</p>
                        <div class="mt-4 space-y-3">
                            @foreach ($liveModes as $mode)
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $mode['name'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $mode['copy'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($workflow as $item)
                <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-600">{{ $item['step'] }}</div>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['copy'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($audiences as $audience)
                <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-5 shadow-sm">
                    <h3 class="text-base font-bold text-slate-950">{{ $audience['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $audience['copy'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1fr_1.1fr]">
            <div class="rounded-[2rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-2xl shadow-slate-950/15">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Safety and parent trust</p>
                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Teacher guidance without device takeover.</h2>
                <p class="mt-5 text-lg leading-8 text-slate-300">
                    "Teachers cannot access the student's computer. They only interact inside the protected ClassBridge AI workspace."
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/8 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Private by design</p>
                        <p class="mt-2 text-sm text-slate-200">No browser history, desktop, or files.</p>
                    </div>
                    <div class="rounded-2xl bg-white/8 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Parent visibility</p>
                        <p class="mt-2 text-sm text-slate-200">Reports and progress can be shared safely.</p>
                    </div>
                    <div class="rounded-2xl bg-white/8 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Protected sessions</p>
                        <p class="mt-2 text-sm text-slate-200">All activity stays inside the classroom.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($features as $feature)
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white/95 p-5 shadow-sm {{ $feature['title'] === 'Live Interactive Classroom' ? 'ring-1 ring-sky-200' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base font-bold text-slate-950">{{ $feature['title'] }}</h3>
                            @if ($feature['title'] === 'Live Interactive Classroom')
                                <span class="rounded-full bg-sky-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-700">Core</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $feature['copy'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600">Private tutor business</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Run your own teaching business without a full school structure.</h2>
                <p class="mt-4 text-sm leading-6 text-slate-600">
                    A tutor account can stand on its own. Add learners, link parents when needed, open live sessions, and keep the classroom professional from day one.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($tutorBenefits as $item)
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-indigo-600">School and academy operations</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Use the same platform for wider organization management.</h2>
                <p class="mt-4 text-sm leading-6 text-slate-600">
                    Schools and academies can manage teachers, classes, students, parents, live sessions, reports, and subscriptions in one organization workspace.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($schoolBenefits as $item)
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Pricing preview</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Plans shaped for tutors, teams, and organizations.</h2>
                <p class="mt-4 text-sm leading-6 text-slate-600">This is a preview of the product structure, not a checkout page. The goal is to match each customer type with the right workspace size.</p>
            </div>

            <div class="mt-8 grid gap-4 xl:grid-cols-4">
                @foreach ($plans as $plan)
                    <div class="rounded-[1.75rem] border border-slate-200 bg-gradient-to-br {{ $plan['tone'] }} p-5 shadow-sm {{ !empty($plan['featured']) ? 'ring-1 ring-sky-200 shadow-lg shadow-sky-950/5' : '' }}">
                        @if ($plan['featured'] ?? false)
                            <span class="inline-flex rounded-full bg-slate-950 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">Most popular</span>
                        @endif
                        <h3 class="mt-3 text-lg font-bold text-slate-950">{{ $plan['name'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $plan['summary'] }}</p>
                        <div class="mt-4 space-y-2">
                            @foreach ($plan['items'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-700">
                                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                    <span>{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="request-demo" class="mx-auto max-w-7xl px-6 py-16 sm:py-20 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-stretch">
            <div class="rounded-[2rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-2xl shadow-slate-950/15 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Final call-to-action</p>
                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Start teaching interactively today.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-300">
                    Build your live classroom around shared teaching, not remote desktop access. Try the demo classroom, start a free trial, or request a guided walkthrough for your school or tutoring business.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-primary-button href="{{ route('register') }}" variant="light">
                        Start Free Trial
                    </x-primary-button>
                    <x-secondary-button href="{{ route('demo.live-classroom') }}" variant="inverse">
                        Try Demo Classroom
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('login') }}" variant="inverse">
                        Login
                    </x-secondary-button>
                </div>

                <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                    The protected classroom keeps teacher and student inside the same workspace while parents, schools, and tutors stay informed through reports and replays.
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">Request demo</p>
                    <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Tell us about your teaching setup.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        We&apos;ll follow up with a demo for your school, tutoring center, private teaching business, homeschool setup, or coding academy.
                    </p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <a href="mailto:demo@classbridge.ai?subject=ClassBridge%20AI%20Demo%20Request" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300 hover:bg-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Email</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">demo@classbridge.ai</p>
                        <p class="mt-1 text-sm text-slate-600">Request a guided walkthrough by email.</p>
                    </a>
                    <a href="tel:+2340000000000" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-slate-300 hover:bg-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Phone</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">+234 000 000 0000</p>
                        <p class="mt-1 text-sm text-slate-600">Speak with the team directly.</p>
                    </a>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                    We can show the live whiteboard, code editor, pointer sync, text pad, chat, parent reporting, and tutor or school workflows.
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
