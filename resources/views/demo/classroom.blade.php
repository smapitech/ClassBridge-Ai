@extends('layouts.public')

@section('title', 'Live Classroom Demo')

@section('content')
<div class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 -z-10 h-[42rem] bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_34%),radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_28%),linear-gradient(180deg,_rgba(248,250,252,1)_0%,_rgba(239,246,255,0.92)_52%,_rgba(248,250,252,1)_100%)]"></div>

    <section class="mx-auto max-w-7xl px-6 pb-10 pt-16 sm:pb-14 sm:pt-20 lg:px-8 lg:pt-24">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:items-center">
            <div class="max-w-3xl">
                <span class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">
                    Protected live classroom preview
                </span>
                <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                    See the live classroom before you start a trial.
                </h1>
                <p class="mt-6 text-lg leading-8 text-slate-600">
                    Teacher and student interact inside the same protected workspace using the whiteboard, code editor, text pad, pointer, chat, and teaching tools.
                    There is no remote desktop access.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-primary-button href="{{ route('register') }}">
                        Start Free Trial
                    </x-primary-button>
                    <x-secondary-button href="{{ route('home') }}#request-demo">
                        Request Demo
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('login') }}">
                        Login
                    </x-secondary-button>
                </div>

                <div class="mt-6 rounded-[1.75rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">
                        Real-time sync will connect teacher and student actions instantly using WebSocket broadcasting.
                    </p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        This demo is a visual preview of the protected classroom shell. The authenticated classroom uses the broadcast-enabled workspace, with polling fallback when needed.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.75rem] border border-white/70 bg-white/95 p-5 shadow-2xl shadow-slate-950/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Room code</p>
                    <p class="mt-3 text-2xl font-black tracking-[0.25em] text-slate-950">CB-DEMO-2048</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-primary-button type="button" data-demo-copy-value="CB-DEMO-2048" class="px-4 py-2 text-xs">
                            Copy code
                        </x-primary-button>
                        <a href="{{ route('register') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                            Join by trial
                        </a>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-white/70 bg-white/95 p-5 shadow-2xl shadow-slate-950/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Live state</p>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-sm font-semibold text-slate-900">Waiting for teacher to start session</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Student joins by room code or link. The teacher guides from inside the same secure workspace.
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-white/70 bg-white/95 p-5 shadow-2xl shadow-slate-950/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Teacher pointer</p>
                    <div class="mt-4 relative h-28 rounded-[1.5rem] bg-[linear-gradient(135deg,_rgba(15,23,42,1)_0%,_rgba(30,41,59,1)_100%)]">
                        <div class="absolute left-4 top-4 rounded-full bg-sky-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white">
                            Active guide
                        </div>
                        <div class="absolute bottom-4 right-4 h-4 w-4 rounded-full bg-sky-400 shadow-[0_0_0_8px_rgba(56,189,248,0.18)]"></div>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-white/70 bg-white/95 p-5 shadow-2xl shadow-slate-950/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Student pointer</p>
                    <div class="mt-4 relative h-28 rounded-[1.5rem] bg-[linear-gradient(135deg,_#f8fafc_0%,_#eef2ff_100%)]">
                        <div class="absolute left-5 top-6 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">
                            Learner cursor visible
                        </div>
                        <div class="absolute bottom-4 left-6 h-4 w-4 rounded-full bg-emerald-500 shadow-[0_0_0_8px_rgba(34,197,94,0.18)]"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 pb-16 lg:px-8">
        <div
            data-demo-classroom
            class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-2xl shadow-slate-950/10"
        >
            <div class="border-b border-slate-200/80 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Live classroom shell</p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <h2 class="text-2xl font-black tracking-tight text-slate-950">Teacher and student in the same protected workspace</h2>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                Protected
                            </span>
                        </div>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                            Teacher can guide, point, correct, explain, and assist. Student can write, draw, type, and code only inside ClassBridge AI.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">Room CB-DEMO-2048</span>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">Join link ready</span>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700">Teacher controls ready</span>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <x-primary-button type="button" data-demo-mode="whiteboard" data-demo-mode-label="Whiteboard Mode" class="px-4 py-2 text-sm">
                        Whiteboard
                    </x-primary-button>
                    <x-secondary-button type="button" data-demo-mode="coding" data-demo-mode-label="Coding Mode" class="px-4 py-2 text-sm">
                        Coding
                    </x-secondary-button>
                    <x-secondary-button type="button" data-demo-mode="text" data-demo-mode-label="Text / English Mode" class="px-4 py-2 text-sm">
                        Text / English
                    </x-secondary-button>
                    <x-secondary-button type="button" data-demo-mode="mathematics" data-demo-mode-label="Mathematics Mode" class="px-4 py-2 text-sm">
                        Mathematics
                    </x-secondary-button>
                    <x-secondary-button type="button" data-demo-mode="presentation" data-demo-mode-label="Presentation Mode" class="px-4 py-2 text-sm">
                        Presentation
                    </x-secondary-button>
                    <span class="ml-auto hidden rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500 lg:inline-flex">
                        Current mode:
                        <span data-demo-mode-preview class="ml-1 text-slate-900">Whiteboard Mode</span>
                    </span>
                </div>
            </div>

            <div class="grid gap-0 xl:grid-cols-[88px_minmax(0,1fr)_372px]">
                <aside class="border-b border-slate-200/80 bg-slate-50/80 p-3 xl:border-b-0 xl:border-r xl:border-slate-200/80">
                    <p class="px-1 pb-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-slate-400">Tools</p>
                    <div class="space-y-2">
                        <div class="rounded-2xl bg-slate-950 px-2 py-3 text-center text-white">
                            <span class="block text-[11px] font-semibold">Move</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3 text-center text-slate-600">
                            <span class="block text-[11px] font-semibold">Pen</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3 text-center text-slate-600">
                            <span class="block text-[11px] font-semibold">Text</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3 text-center text-slate-600">
                            <span class="block text-[11px] font-semibold">Box</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-2 py-3 text-center text-slate-600">
                            <span class="block text-[11px] font-semibold">Erase</span>
                        </div>
                    </div>
                </aside>

                <section class="min-h-[42rem] border-b border-slate-200/80 bg-[radial-gradient(circle_at_top_left,_rgba(148,163,184,0.1),_transparent_25%),linear-gradient(180deg,_#f8fafc_0%,_#eef2ff_100%)] xl:border-b-0 xl:border-r xl:border-slate-200/80">
                    <div class="border-b border-slate-200/80 px-5 py-4 sm:px-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-primary-button type="button" data-demo-tab="whiteboard" class="px-4 py-2 text-sm">
                                Whiteboard area
                            </x-primary-button>
                            <x-secondary-button type="button" data-demo-tab="code" class="px-4 py-2 text-sm">
                                Code editor panel
                            </x-secondary-button>
                            <x-secondary-button type="button" data-demo-tab="text" class="px-4 py-2 text-sm">
                                Text pad tab
                            </x-secondary-button>
                            <x-status-badge tone="info" class="ml-auto">
                                Demo preview
                            </x-status-badge>
                        </div>
                    </div>

                    <div class="min-h-[38rem]">
                        <div data-demo-panel="whiteboard" class="h-full p-5 sm:p-6">
                            <div class="relative h-full overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-inner">
                                <div class="absolute inset-0 bg-[linear-gradient(to_right,_rgba(148,163,184,0.08)_1px,_transparent_1px),linear-gradient(to_bottom,_rgba(148,163,184,0.08)_1px,_transparent_1px)] bg-[size:32px_32px]"></div>

                                <div class="absolute left-5 top-5 rounded-full bg-sky-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white shadow-lg shadow-sky-500/20">
                                    Teacher pointer placeholder
                                </div>
                                <div class="absolute right-6 top-8 rounded-full bg-emerald-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white shadow-lg shadow-emerald-500/20">
                                    Student pointer placeholder
                                </div>

                                <div class="absolute left-[18%] top-[20%] rounded-3xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800 shadow-lg shadow-sky-950/5">
                                    Teacher draws a line here
                                </div>
                                <div class="absolute left-[48%] top-[48%] rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-lg shadow-emerald-950/5">
                                    Student writes a correction here
                                </div>
                                <div class="absolute left-[32%] top-[68%] rounded-3xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 shadow-lg shadow-amber-950/5">
                                    Same workspace, same time
                                </div>

                                <div class="absolute bottom-5 left-5 rounded-[1.5rem] border border-slate-200 bg-white/95 px-4 py-3 text-sm text-slate-600 shadow-xl shadow-slate-950/10">
                                    <p class="font-semibold text-slate-900">Whiteboard area</p>
                                    <p class="mt-1">Teacher can point, correct, and explain without touching the learner&apos;s device.</p>
                                </div>
                            </div>
                        </div>

                        <div data-demo-panel="code" class="hidden h-full p-5 sm:p-6">
                            <div class="grid h-full gap-5 lg:grid-cols-[minmax(0,1fr)_250px]">
                                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-950 p-5 text-white shadow-xl shadow-slate-950/10">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Shared code editor</p>
                                            <h3 class="mt-1 text-xl font-bold">Teacher edits and student sees the change</h3>
                                        </div>
                                        <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-300">Synced</span>
                                    </div>
                                    <pre class="mt-6 overflow-x-auto rounded-[1.5rem] border border-white/10 bg-white/5 p-4 text-sm leading-7 text-sky-100"><code>function explainFractions() {
    const numerator = 3;
    const denominator = 4;
    return &quot;Three out of four parts&quot;;
}</code></pre>
                                    <div class="mt-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-4 text-sm leading-6 text-slate-200">
                                        Shared code editor panel. Teacher and learner stay in the same protected classroom instead of on the child&apos;s desktop.
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Live preview</p>
                                        <div class="mt-4 rounded-[1.5rem] bg-slate-50 p-4 font-mono text-xs leading-6 text-slate-700">
                                            <p>&lt;h1&gt;ClassBridge AI&lt;/h1&gt;</p>
                                            <p>&lt;p&gt;Teach online like you are beside the child&lt;/p&gt;</p>
                                            <p class="text-emerald-700">// Teacher correction appears instantly</p>
                                        </div>
                                    </div>

                                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Sync note</p>
                                        <p class="mt-3 text-sm leading-6 text-slate-600">
                                            Coding actions can be broadcast to the teacher and student room so the assistant can guide the learner step by step.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div data-demo-panel="text" class="hidden h-full p-5 sm:p-6">
                            <div class="grid h-full gap-5 lg:grid-cols-[minmax(0,1fr)_250px]">
                                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Text / English pad</p>
                                            <h3 class="mt-1 text-xl font-bold text-slate-950">Shared writing and corrections</h3>
                                        </div>
                                        <span class="rounded-full bg-sky-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-700">Live notes</span>
                                    </div>
                                    <div class="mt-6 rounded-[1.5rem] bg-[linear-gradient(180deg,_#ffffff_0%,_#f8fafc_100%)] p-5 text-sm leading-7 text-slate-700 shadow-inner">
                                        <p class="font-semibold text-slate-900">Teacher:</p>
                                        <p>Write the opening paragraph together. We can highlight mistakes and improve the sentence without leaving the classroom.</p>
                                        <p class="mt-5 font-semibold text-slate-900">Student:</p>
                                        <p>I can edit the paragraph in the same protected workspace and the teacher sees the typing immediately.</p>
                                        <p class="mt-5 border-l-4 border-sky-500 bg-sky-50 px-4 py-3 text-sky-800">
                                            TODO: connect this preview shell to a seeded live text room when the public demo is upgraded from static mock to interactive sandbox.
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Text pad tab</p>
                                        <div class="mt-4 space-y-2 rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">
                                            <p class="rounded-2xl bg-white px-3 py-2 shadow-sm">Shared typing space</p>
                                            <p class="rounded-2xl bg-white px-3 py-2 shadow-sm">Teacher corrections</p>
                                            <p class="rounded-2xl bg-white px-3 py-2 shadow-sm">Learner response</p>
                                        </div>
                                    </div>

                                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Instant guidance</p>
                                        <p class="mt-3 text-sm leading-6 text-slate-600">
                                            The teacher can guide line by line while the learner types, with the pointer and shared pad staying inside the protected classroom.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-0 bg-white">
                    <div class="border-b border-slate-200/80 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Session controls</p>
                                <p class="mt-1 text-sm text-slate-500">Teacher starts, ends, and manages the room from here.</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Teacher view</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <x-primary-button type="button">
                                Start live session
                            </x-primary-button>
                            <x-secondary-button type="button" variant="danger">
                                End session
                            </x-secondary-button>
                            <x-status-badge tone="warning">
                                Join state: waiting
                            </x-status-badge>
                        </div>

                        <div class="mt-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Student permissions</p>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold text-slate-600">
                                <span class="rounded-2xl bg-white px-3 py-2 shadow-sm">Allow drawing</span>
                                <span class="rounded-2xl bg-white px-3 py-2 shadow-sm">Allow typing</span>
                                <span class="rounded-2xl bg-white px-3 py-2 shadow-sm">Allow chat</span>
                                <span class="rounded-2xl bg-white px-3 py-2 shadow-sm">Allow code editing</span>
                                <span class="col-span-2 rounded-2xl bg-white px-3 py-2 shadow-sm">Show / hide student pointer</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200/80 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Classroom mode selector</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <x-primary-button type="button" class="px-3 py-1.5 text-xs">
                                Whiteboard
                            </x-primary-button>
                            <x-secondary-button type="button" class="px-3 py-1.5 text-xs">
                                Coding
                            </x-secondary-button>
                            <x-secondary-button type="button" class="px-3 py-1.5 text-xs">
                                Text / English
                            </x-secondary-button>
                            <x-secondary-button type="button" class="px-3 py-1.5 text-xs">
                                Math
                            </x-secondary-button>
                            <x-secondary-button type="button" class="px-3 py-1.5 text-xs">
                                Presentation
                            </x-secondary-button>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">
                            Current mode: <span data-demo-mode-preview class="font-semibold text-slate-950">Whiteboard Mode</span>
                        </p>
                    </div>

                    <div class="border-b border-slate-200/80 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Chat</p>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <span class="font-semibold text-slate-950">Teacher:</span> Let us solve this together.
                            </div>
                            <div class="rounded-2xl bg-slate-950 px-4 py-3 text-sm text-white">
                                <span class="font-semibold text-white">Student:</span> I can see the correction instantly.
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <span class="font-semibold text-slate-950">Parent:</span> Great, the progress is visible in the report.
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200/80 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Participants</p>
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">Amina - Teacher / Tutor</p>
                                    <p class="text-xs text-slate-500">Pointer visible</p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">Teacher</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">Emmanuel - Learner</p>
                                    <p class="text-xs text-slate-500">Allowed to type and chat</p>
                                </div>
                                <span class="rounded-full bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700">Student</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-950">Parent observer</p>
                                    <p class="text-xs text-slate-500">Reports only</p>
                                </div>
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">Parent</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200/80 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Student view</p>
                        <div class="mt-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-950">Join session</p>
                            <p class="mt-1 text-sm text-slate-600">The learner enters with a code or link, then uses only the tools the teacher allows.</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                <span class="rounded-full bg-white px-3 py-1.5 shadow-sm">See teacher pointer</span>
                                <span class="rounded-full bg-white px-3 py-1.5 shadow-sm">Chat if allowed</span>
                                <span class="rounded-full bg-white px-3 py-1.5 shadow-sm">Draw if allowed</span>
                                <span class="rounded-full bg-white px-3 py-1.5 shadow-sm">Code if allowed</span>
                            </div>
                            <div class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                Protected workspace only. No remote desktop access.
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200/80 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Room link</p>
                        <div class="mt-4 rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                            <p class="text-sm font-semibold text-slate-950">Demo join link</p>
                            <p class="mt-1 break-all text-sm text-slate-600">https://classbridge.ai/live-classroom-demo</p>
                            <x-primary-button type="button" data-demo-copy-value="https://classbridge.ai/live-classroom-demo" class="mt-3 px-4 py-2 text-xs">
                                Copy link
                            </x-primary-button>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Safety notice</p>
                        <div class="mt-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm leading-6 text-slate-700">
                                “ClassBridge AI is a protected learning workspace. Teachers and students interact only inside this classroom. Teachers cannot access the student&apos;s computer, files, desktop, browser history, or other applications.”
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 pb-20 lg:px-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">What is real now</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    The authenticated classroom page already has the WebSocket-ready workspace, participant list, chat, pointers, whiteboard, code editor, text pad, and session controls.
                </p>
            </div>
            <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">What is placeholder</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    This public preview is a polished visual mock, so visitors can understand the classroom before login.
                </p>
            </div>
            <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">What remains</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Full collaborative editing polish, richer per-user pointer trails, and replay-grade event persistence can still be expanded.
                </p>
            </div>
            <div class="rounded-[1.75rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Best next step</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Start a free trial or request a demo so a teacher can launch a room and a student can join by code.
                </p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 pb-24 lg:px-8">
        <div class="rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,_#0f172a_0%,_#1e293b_100%)] px-6 py-8 text-white shadow-2xl shadow-slate-950/10 sm:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Start here</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight">Start teaching interactively today.</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        Open a protected live classroom where teachers and students work side by side without exposing the learner&apos;s device.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-primary-button href="{{ route('register') }}" variant="light">
                        Start Free Trial
                    </x-primary-button>
                    <x-secondary-button href="{{ route('home') }}#request-demo" variant="inverse">
                        Request Demo
                    </x-secondary-button>
                    <x-secondary-button href="{{ route('login') }}" variant="inverse">
                        Login
                    </x-secondary-button>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-demo-classroom]');
    if (!root) {
        return;
    }

    const tabs = [...root.querySelectorAll('[data-demo-tab]')];
    const panels = [...root.querySelectorAll('[data-demo-panel]')];
    const modes = [...root.querySelectorAll('[data-demo-mode]')];
    const modePreviewLabels = [...root.querySelectorAll('[data-demo-mode-preview]')];
    const copyButtons = [...root.querySelectorAll('[data-demo-copy-value]')];

    const setActive = (items, active, activeClasses, inactiveClasses) => {
        items.forEach((item) => {
            const isActive = item.dataset.demoTab === active || item.dataset.demoMode === active;
            item.classList.remove(...activeClasses, ...inactiveClasses);
            item.classList.add(...(isActive ? activeClasses : inactiveClasses));
        });
    };

    const setTab = (tab) => {
        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.demoPanel !== tab);
        });

        setActive(
            tabs,
            tab,
            ['bg-slate-950', 'text-white'],
            ['border', 'border-slate-200', 'bg-white', 'text-slate-700', 'hover:border-slate-300', 'hover:text-slate-900']
        );
    };

    const setMode = (mode, label) => {
        modePreviewLabels.forEach((node) => {
            node.textContent = label;
        });

        setActive(
            modes,
            mode,
            ['bg-slate-950', 'text-white'],
            ['border', 'border-slate-200', 'bg-white', 'text-slate-700', 'hover:border-slate-300', 'hover:text-slate-900']
        );
    };

    tabs.forEach((button) => {
        button.addEventListener('click', () => setTab(button.dataset.demoTab || 'whiteboard'));
    });

    modes.forEach((button) => {
        button.addEventListener('click', () => setMode(button.dataset.demoMode || 'whiteboard', button.dataset.demoModeLabel || button.textContent.trim()));
    });

    copyButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.demoCopyValue || '';
            try {
                await navigator.clipboard.writeText(value);
                const original = button.textContent;
                button.textContent = 'Copied';
                window.setTimeout(() => {
                    button.textContent = original;
                }, 1400);
            } catch (error) {
                console.error(error);
            }
        });
    });

    setTab('whiteboard');
    setMode('whiteboard', 'Whiteboard Mode');
});
</script>
@endpush
