@extends('layouts.dashboard')
@section('title', $session->title)

@php
    $files = collect($sessionSnapshot['files'] ?? []);
    $participants = collect($sessionSnapshot['participants'] ?? []);
    $lessonSteps = collect($sessionSnapshot['lesson_steps'] ?? []);
    $messages = collect($sessionSnapshot['messages'] ?? []);
    $activeFileKey = $sessionSnapshot['active_file_key'] ?? ($currentFile?->filename ?? $files->first()?->filename ?? 'index.html');
    $statusLabel = ucfirst($sessionSnapshot['status'] ?? $session->status ?? 'waiting');
    $currentFile = $currentFile ?? $files->firstWhere('filename', $activeFileKey) ?? $files->first();
    $instructionCopy = $session->assignment?->instructions ?: $session->assignment?->description ?: 'No lesson instructions have been added yet.';
    $sessionPayload = [
        'session' => [
            'id' => $session->id,
            'title' => $session->title,
            'status' => $sessionSnapshot['status'] ?? $session->status,
            'join_code' => $session->join_code,
            'join_link' => $joinLink,
            'lesson_mode' => $session->lesson_mode,
            'active_file_key' => $activeFileKey,
            'metadata' => $sessionSnapshot['metadata'] ?? ($session->metadata ?? []),
            'started_at' => optional($session->started_at)->toIso8601String(),
            'ended_at' => optional($session->ended_at)->toIso8601String(),
            'last_saved_at' => optional($session->last_saved_at)->toIso8601String(),
        ],
        'permissions' => $permissions,
        'files' => $files->values()->map(fn ($file) => [
            'id' => $file->id,
            'filename' => $file->filename,
            'language' => $file->language,
            'content' => (string) ($file->content ?? ''),
            'sort_order' => $file->sort_order,
            'is_entry_point' => (bool) $file->is_entry_point,
        ])->all(),
        'participants' => $participants->values()->map(fn ($participant) => [
            'id' => $participant->id,
            'user_id' => $participant->user_id,
            'name' => $participant->user?->displayName(),
            'email' => $participant->user?->email,
            'role' => $participant->role_in_session,
            'is_active' => (bool) $participant->is_active,
            'typing_status' => $participant->typing_status,
            'cursor_line' => $participant->cursor_line,
            'cursor_column' => $participant->cursor_column,
            'active_file_key' => $participant->active_file_key,
            'permissions' => $participant->permissions ?? [],
        ])->all(),
        'messages' => $messages->map(fn ($message) => [
            'id' => $message->id,
            'user_id' => $message->user_id,
            'user_name' => $message->user?->displayName(),
            'message' => $message->message,
            'message_type' => $message->message_type,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ])->all(),
        'events' => collect($sessionSnapshot['events'] ?? [])->map(fn ($event) => [
            'id' => $event->id,
            'user_id' => $event->user_id,
            'user_name' => $event->user?->displayName(),
            'event_type' => $event->event_type,
            'title' => $event->title,
            'description' => $event->description,
            'payload' => $event->payload ?? [],
            'occurred_at' => optional($event->occurred_at)->toIso8601String(),
        ])->values()->all(),
        'lesson_steps' => $lessonSteps->values()->map(fn ($step, $index) => [
            'id' => $step['id'] ?? $index + 1,
            'title' => $step['title'] ?? ('Step ' . ($index + 1)),
            'description' => $step['description'] ?? '',
            'is_done' => (bool) ($step['is_done'] ?? false),
        ])->all(),
        'current_user' => [
            'id' => Auth::id(),
            'name' => Auth::user()->displayName(),
            'is_teacher' => $isTeacher,
        ],
    ];
@endphp

@push('head')
<meta name="coding-session-id" content="{{ $session->id }}">
<meta name="coding-user-id" content="{{ Auth::id() }}">
<meta name="coding-is-teacher" content="{{ $isTeacher ? '1' : '0' }}">
<meta name="coding-join-code" content="{{ $session->join_code }}">
<meta name="coding-join-link" content="{{ $joinLink }}">
<meta name="coding-active-file" content="{{ $activeFileKey }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script type="application/json" id="coding-session-data">@json($sessionPayload)</script>
@endpush

@section('content')
<div data-coding-studio class="space-y-6">
    <section class="cb-surface px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
                <x-status-badge tone="info">Live Coding Studio</x-status-badge>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Protected online IDE-style learning workspace</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">{{ $session->title }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600">
                    Teacher and student work inside the same coding environment at the same time using a shared editor, preview, chat, lesson steps, and collaboration controls. This is not remote desktop.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <x-status-badge tone="{{ $statusLabel === 'Live' ? 'success' : ($statusLabel === 'Ended' ? 'danger' : 'warning') }}">
                        {{ $statusLabel }}
                    </x-status-badge>
                    <x-status-badge tone="neutral">
                        {{ $isTeacher ? 'Teacher / Tutor control' : (Auth::user()->isParent() ? 'Parent observer' : 'Student / Learner view') }}
                    </x-status-badge>
                    <x-status-badge tone="neutral">
                        {{ $session->lesson_mode ? strtoupper($session->lesson_mode) : 'MIXED' }}
                    </x-status-badge>
                    <x-status-badge tone="neutral" id="coding-sync-badge">
                        Connecting...
                    </x-status-badge>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($isTeacher)
                        @if (($sessionSnapshot['status'] ?? $session->status) === 'live')
                            <form method="POST" action="{{ route('coding.sessions.end', $session) }}" onsubmit="return confirm('End this live coding session for everyone?')">
                                @csrf
                                <x-secondary-button type="submit" variant="danger">
                                    End session
                                </x-secondary-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('coding.sessions.start', $session) }}">
                                @csrf
                                <x-primary-button type="submit">
                                    Start session
                                </x-primary-button>
                            </form>
                        @endif
                    @endif

                    <x-secondary-button type="button" id="run-code-btn">
                        Run Code
                    </x-secondary-button>
                    <x-secondary-button type="button" id="save-code-btn">
                        Save
                    </x-secondary-button>
                    <x-secondary-button type="button" id="format-code-btn">
                        Format
                    </x-secondary-button>
                    <x-secondary-button type="button" id="reset-code-btn">
                        Reset
                    </x-secondary-button>
                    <x-secondary-button type="button" id="submit-work-btn">
                        Submit
                    </x-secondary-button>
                    <x-secondary-button type="button" id="share-session-btn" data-copy-text="{{ $joinLink }}">
                        Share Session
                    </x-secondary-button>
                    <x-secondary-button type="button" id="invite-session-btn" data-copy-text="{{ $joinLink }}">
                        Invite Student / Teacher
                    </x-secondary-button>
                    <x-secondary-button type="button" id="theme-toggle">
                        Theme
                    </x-secondary-button>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:w-[30rem]">
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Join code</p>
                    <p class="mt-2 text-lg font-black tracking-[0.18em] text-slate-900">{{ $session->join_code }}</p>
                    <x-primary-button type="button" data-copy-text="{{ $session->join_code }}" class="mt-4 px-4 py-2 text-xs">
                        Copy code
                    </x-primary-button>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Join link</p>
                    <p class="mt-2 truncate text-sm font-semibold text-slate-900">{{ $joinLink }}</p>
                    <x-secondary-button type="button" data-copy-text="{{ $joinLink }}" class="mt-4 px-4 py-2 text-xs">
                        Copy link
                    </x-secondary-button>
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Live session status</p>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                        <p id="coding-session-status-text" class="text-sm font-semibold text-slate-700">Real-time sync will connect teacher and student actions instantly using WebSocket broadcasting.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-[1.5rem] border border-sky-200 bg-sky-50 px-4 py-4 text-sm leading-6 text-sky-800">
            <span class="font-semibold">Safety notice:</span>
            ClassBridge AI is a protected learning workspace. Teachers and students interact only inside this classroom. Teachers cannot access the student&apos;s computer, files, desktop, browser history, or other applications.
        </div>
    </section>

    <div class="grid gap-4 md:hidden">
        <div class="flex gap-2 rounded-[1.5rem] border border-slate-200 bg-white p-2 shadow-sm">
            <button type="button" data-coding-mobile-tab="editor" class="flex-1 rounded-full bg-slate-950 px-3 py-2 text-sm font-semibold text-white">Editor</button>
            <button type="button" data-coding-mobile-tab="preview" class="flex-1 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600">Preview</button>
            <button type="button" data-coding-mobile-tab="chat" class="flex-1 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600">Chat</button>
            <button type="button" data-coding-mobile-tab="lesson" class="flex-1 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600">Lesson</button>
        </div>
    </div>

    <section class="grid gap-6 xl:grid-cols-[300px_minmax(0,1fr)_340px]">
        <aside data-coding-mobile-panel="lesson" class="space-y-4">
            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Files</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Project files</h2>
                    </div>
                    @if ($isTeacher)
                        <button type="button" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600" id="add-file-btn">
                            + File
                        </button>
                    @endif
                </div>

                <div id="coding-file-list" class="mt-4 space-y-2">
                    @foreach ($files as $file)
                        <button
                            type="button"
                            data-coding-file-key="{{ $file->filename }}"
                            class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition {{ $file->filename === $activeFileKey ? 'border-slate-950 bg-slate-950 text-white shadow-lg shadow-slate-950/10' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300' }}"
                        >
                            <span class="truncate text-sm font-semibold">{{ $file->filename }}</span>
                            <span class="ml-3 rounded-full border border-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em] {{ $file->filename === $activeFileKey ? 'bg-white/10 text-white' : 'bg-slate-50 text-slate-500' }}">
                                {{ strtoupper($file->language) }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Lesson steps</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Guided lesson</h2>
                    </div>
                    @if ($isTeacher)
                        <button type="button" id="add-lesson-step-btn" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
                            + Step
                        </button>
                    @endif
                </div>

                <div id="coding-lesson-steps" class="mt-4 space-y-3">
                    @forelse ($lessonSteps as $index => $step)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="cb-badge bg-white text-slate-500">Step {{ $index + 1 }}</span>
                                @if (($step['is_done'] ?? false))
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Done</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm font-semibold text-slate-900">{{ $step['title'] ?? 'Lesson step' }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $step['description'] ?? '' }}</p>
                        </div>
                    @empty
                        <x-empty-state
                            title="No lesson steps yet"
                            description="Add guided steps so the student can follow the coding lesson one step at a time."
                            tone="info"
                        />
                    @endforelse
                </div>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Participants</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Who is here</h2>
                    </div>
                    <x-status-badge tone="success">{{ $participants->count() }}</x-status-badge>
                </div>

                <div id="coding-participants" class="mt-4 space-y-3">
                    @forelse ($participants as $participant)
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $participant->user?->displayName() ?? 'Participant' }}</p>
                                <p class="text-xs text-slate-500">{{ $participant->role_in_session === 'teacher' ? 'Teacher / Tutor' : ($participant->role_in_session === 'observer' ? 'Observer' : 'Learner') }}</p>
                                @if ($participant->typing_status)
                                    <p class="mt-1 text-[11px] font-semibold text-sky-600">{{ ucfirst(str_replace('_', ' ', $participant->typing_status)) }}</p>
                                @endif
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $participant->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $participant->is_active ? 'Active' : 'Away' }}
                            </span>
                        </div>
                    @empty
                        <x-empty-state
                            title="No participants yet"
                            description="When the teacher or student joins, the session will appear here."
                            tone="neutral"
                        />
                    @endforelse
                </div>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Collaboration</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Teacher controls</h2>
                    </div>
                    <x-status-badge tone="{{ $isTeacher ? 'success' : 'neutral' }}">{{ $isTeacher ? 'Teacher' : 'Student' }}</x-status-badge>
                </div>

                @if ($isTeacher)
                    <div class="mt-4 space-y-3">
                        @foreach (['edit' => 'Allow student editing', 'chat' => 'Allow student chat', 'pointer' => 'Show student pointer', 'code' => 'Allow code editing'] as $key => $label)
                            <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <span>{{ $label }}</span>
                                <input type="checkbox" data-coding-permission-toggle="{{ $key }}" class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-400">
                            </label>
                        @endforeach

                        <div class="flex flex-wrap gap-2 pt-2">
                            <button type="button" id="take-control-btn" class="rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Take control</button>
                            <button type="button" id="release-control-btn" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Give control back</button>
                            <button type="button" id="highlight-selection-btn" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Highlight line</button>
                            <button type="button" id="save-session-btn" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">Save session</button>
                        </div>
                    </div>
                @else
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <button type="button" id="request-help-btn" class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-left text-sm font-semibold text-sky-700">Request help</button>
                        <button type="button" id="raise-hand-btn" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-sm font-semibold text-amber-700">Raise hand</button>
                        <button type="button" id="submit-work-btn" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-left text-sm font-semibold text-emerald-700 sm:col-span-2">Submit work for review</button>
                    </div>
                @endif
            </div>
        </aside>

        <div data-coding-mobile-panel="editor" class="space-y-4">
            <section class="cb-surface p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Shared editor</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Same code, same room, same time</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="coding-typing-status" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Ready</span>
                        <span id="coding-save-label" class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Saved</span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2" id="coding-file-tabs">
                    @foreach ($files as $file)
                        <button
                            type="button"
                            data-coding-file-tab="{{ $file->filename }}"
                            class="cb-ide-tab {{ $file->filename === $activeFileKey ? 'cb-ide-tab-active' : 'cb-ide-tab-inactive' }}"
                        >
                            <span>{{ $file->filename }}</span>
                            <span class="rounded-full border border-white/20 px-2 py-0.5 text-[10px] uppercase tracking-[0.18em]">{{ strtoupper($file->language) }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-slate-950/95 p-3 shadow-inner">
                    <div class="flex items-center justify-between gap-3 border-b border-white/10 px-3 py-2 text-xs text-slate-300">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span id="coding-sync-status">Online</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span id="coding-active-file-label">{{ $currentFile?->filename ?? 'index.html' }}</span>
                            <span id="coding-cursor-label">line 1, col 1</span>
                        </div>
                    </div>
                    <div id="coding-editor" class="min-h-[32rem] rounded-[1.25rem] bg-slate-950"></div>
                </div>
            </section>

            <section data-coding-mobile-panel="preview" class="cb-surface p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Output</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Preview, console, errors, and tests</h2>
                    </div>
                    <div class="flex flex-wrap gap-2" id="coding-output-tabs">
                        @foreach (['preview' => 'Preview', 'console' => 'Console', 'errors' => 'Errors', 'tests' => 'Test Results'] as $key => $label)
                            <button type="button" data-coding-output-tab="{{ $key }}" class="cb-ide-tab {{ $key === 'preview' ? 'cb-ide-tab-active' : 'cb-ide-tab-inactive' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white">
                    <div data-coding-output-panel="preview" class="h-[28rem]">
                        <iframe id="coding-preview-frame" class="h-full w-full border-0 bg-white" sandbox="allow-scripts allow-forms allow-modals" title="Coding preview"></iframe>
                    </div>
                    <div data-coding-output-panel="console" class="hidden h-[28rem]">
                        <div id="coding-console" class="h-full overflow-y-auto bg-slate-950 p-4 font-mono text-sm text-slate-100"></div>
                    </div>
                    <div data-coding-output-panel="errors" class="hidden h-[28rem]">
                        <div id="coding-errors" class="h-full overflow-y-auto bg-rose-50 p-4 text-sm text-rose-700"></div>
                    </div>
                    <div data-coding-output-panel="tests" class="hidden h-[28rem]">
                        <div id="coding-tests" class="h-full overflow-y-auto bg-emerald-50 p-4 text-sm text-emerald-800">
                            <p class="font-semibold">Friendly test results</p>
                            <p class="mt-2">Run the code to see a simple pass/fail summary here.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside data-coding-mobile-panel="chat" class="space-y-4">
            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Chat</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Live discussion</h2>
                    </div>
                    <x-status-badge tone="success">Session chat</x-status-badge>
                </div>

                <div id="coding-chat-messages" class="mt-4 max-h-[20rem] space-y-3 overflow-y-auto pr-1">
                    @forelse ($messages as $message)
                        <div class="flex {{ (int) $message->user_id === (int) Auth::id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm {{ (int) $message->user_id === (int) Auth::id() ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-800' }}">
                                <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] {{ (int) $message->user_id === (int) Auth::id() ? 'text-slate-300' : 'text-slate-500' }}">
                                    <span>{{ $message->user?->displayName() ?? 'User' }}</span>
                                    <span>{{ optional($message->created_at)->format('g:i A') }}</span>
                                </div>
                                <p class="mt-2 whitespace-pre-wrap leading-6">{{ $message->message }}</p>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            title="Chat is empty"
                            description="Teacher and student messages will appear here during the live session."
                            tone="neutral"
                        />
                    @endforelse
                </div>

                <div class="mt-4 space-y-3">
                    <textarea id="coding-chat-input" rows="4" class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Ask a question, give an instruction, or share a hint..."></textarea>
                    <div class="flex items-center gap-3">
                        <button type="button" id="coding-chat-send" class="cb-btn-primary flex-1">Send message</button>
                        <button type="button" id="coding-chat-note" class="cb-btn-secondary">Add note</button>
                    </div>
                </div>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Instructions</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Lesson guidance</h2>
                    </div>
                    <x-status-badge tone="info">Teacher led</x-status-badge>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-600 whitespace-pre-line">{{ $instructionCopy }}</p>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">AI support</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Teaching assistant placeholder</h2>
                    </div>
                    <x-status-badge tone="purple">Support only</x-status-badge>
                </div>
                <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                    <div class="rounded-2xl bg-violet-50 px-4 py-3 text-violet-700">Explain code in simple words.</div>
                    <div class="rounded-2xl bg-violet-50 px-4 py-3 text-violet-700">Suggest a correction without replacing the teacher.</div>
                    <div class="rounded-2xl bg-violet-50 px-4 py-3 text-violet-700">Generate a quick practice task from the current lesson.</div>
                </div>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="cb-page-kicker">Class notes</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Review notes</h2>
                    </div>
                </div>
                <div id="coding-class-notes" class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        TODO: connect lesson notes, corrections, and teacher highlights to this panel.
                    </div>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
