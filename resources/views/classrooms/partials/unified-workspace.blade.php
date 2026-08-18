@php
    $normalizedMode = \App\Enums\LiveLessonMode::normalize($currentMode ?? 'whiteboard');
    $activePanelKey = $normalizedMode === 'mathematics' ? 'whiteboard' : $normalizedMode;
    $codeTabs = collect($codeFiles ?? []);
    $lessonResources = collect($sessionResources ?? []);
    $effectivePermissions = data_get($session?->metadata, 'student_permissions', $roomPermissions ?? []);
    $participantCount = $session?->participants?->where('is_active', true)->count() ?? 0;
    $statusLabel = $session?->status === 'live'
        ? 'Live now'
        : ($session?->status === 'ended'
            ? 'Ended'
            : ($session?->status === 'waiting' ? 'Waiting room' : 'Ready'));
    $modeLabel = \App\Enums\LiveLessonMode::label($normalizedMode);
    $joinCode = $classroom->room_code;
@endphp

<div class="space-y-4" x-data="{ rightPanelOpen: true }">
    <section class="rounded-[1.5rem] border border-slate-200 bg-white px-4 py-4 shadow-sm sm:px-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Room code</p>
                    <div class="mt-1 flex items-center gap-2">
                        <p class="text-sm font-black tracking-[0.22em] text-slate-900">{{ $joinCode }}</p>
                        <button type="button" data-copy-text="{{ $joinCode }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" aria-label="Copy room code">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8a2 2 0 002-2V6m-2 8V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Current mode</p>
                    <p id="classroom-mode-badge" class="mt-1 text-sm font-black text-slate-900">{{ $modeLabel }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Live status</p>
                    <p id="activity-status" class="mt-1 text-sm font-black text-emerald-700">{{ $statusLabel }}</p>
                    <span id="session-connection-status" class="mt-1 block text-[11px] font-medium text-slate-500">Real-time sync ready</span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">Participants</p>
                    <p class="mt-1 text-sm font-black text-slate-900"><span id="participant-count">{{ $participantCount }}</span> connected</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                <x-secondary-button type="button" data-copy-text="{{ $joinCode }}" class="shrink-0 px-4 py-3 text-sm">
                    Copy room code
                </x-secondary-button>
                <x-secondary-button type="button" data-copy-text="{{ $roomJoinLink }}" class="shrink-0 px-4 py-3 text-sm">
                    Copy join link
                </x-secondary-button>
                <x-secondary-button type="button" class="shrink-0 px-4 py-3 text-sm" @click="rightPanelOpen = !rightPanelOpen" x-text="rightPanelOpen ? 'Hide panel' : 'Show panel'">
                    Hide panel
                </x-secondary-button>
                @if ($session)
                    <x-secondary-button type="button" id="save-session-btn" class="shrink-0 px-4 py-3 text-sm">
                        Save session
                    </x-secondary-button>
                    <span id="session-save-status" class="self-center text-xs font-medium text-slate-500">Saved</span>
                @endif

                @if ($isTeacher && (($session?->status ?? $classroom->status) !== 'live'))
                    <form method="POST" action="{{ route('classrooms.start-session', $classroom) }}">
                        @csrf
                        <x-primary-button type="submit" class="shrink-0 px-4 py-3 text-sm">
                            Start session
                        </x-primary-button>
                    </form>
                @endif

                @if ($isTeacher && (($session?->status ?? $classroom->status) === 'live'))
                    <form method="POST" action="{{ route('classrooms.end-session', $classroom) }}" onsubmit="return confirm('End this live lesson for everyone?')">
                        @csrf
                        <x-secondary-button type="submit" variant="danger" class="shrink-0 px-4 py-3 text-sm">
                            End session
                        </x-secondary-button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="rounded-[1.5rem] border border-slate-200 bg-white p-2 shadow-sm">
        <div class="grid gap-2 sm:grid-cols-5">
            @foreach ([
                'whiteboard' => ['label' => 'Whiteboard'],
                'coding' => ['label' => 'Coding Studio'],
                'text' => ['label' => 'Text Pad'],
                'mathematics' => ['label' => 'Mathematics'],
                'presentation' => ['label' => 'Presentation'],
            ] as $modeKey => $label)
                <button
                    type="button"
                    data-mode-button
                    data-mode="{{ $modeKey }}"
                    @class([
                        'cb-classroom-mode-tab',
                        'cb-classroom-mode-tab-active' => $normalizedMode === $modeKey,
                        'cb-classroom-mode-tab-inactive' => $normalizedMode !== $modeKey,
                    ])
                    aria-pressed="{{ $normalizedMode === $modeKey ? 'true' : 'false' }}"
                    aria-label="{{ $label['label'] }}"
                >
                    <span class="cb-classroom-mode-tab-icon">
                        @switch($modeKey)
                            @case('whiteboard')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M6 5v14m12-14v14M8 19h8"/>
                                </svg>
                                @break
                            @case('coding')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"/>
                                </svg>
                                @break
                            @case('text')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10"/>
                                </svg>
                                @break
                            @case('mathematics')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h12M6 12h12M6 18h12"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8l2 8m2-8-2 8"/>
                                </svg>
                                @break
                            @case('presentation')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v10H4z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v4m-3 0h6"/>
                                </svg>
                                @break
                        @endswitch
                    </span>
                    <span class="truncate">{{ $label['label'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    @if ($session)
        <div class="grid gap-4" :class="rightPanelOpen ? 'xl:grid-cols-[minmax(0,1fr)_360px]' : 'xl:grid-cols-1'">
            <div class="min-w-0 space-y-4">
                @include('classrooms.partials.whiteboard-studio')

                <section data-workspace-panel="coding" @class(['cb-ide-shell p-3 sm:p-4 space-y-3', 'hidden' => $activePanelKey !== 'coding'])>
                    <div class="flex flex-wrap items-center justify-between gap-3 px-1">
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-900">Coding Studio</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                <p id="code-file-title" class="truncate text-sm font-semibold text-slate-900">index.html</p>
                                <span id="code-language-label" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">HTML</span>
                                <span id="code-status" class="text-xs font-medium text-slate-500">Ready to edit</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-start">
                        <section class="overflow-hidden rounded-[1.25rem] border border-slate-800 bg-slate-950 shadow-2xl shadow-slate-950/20">
                            <div class="flex flex-col gap-2 border-b border-white/10 px-3 py-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto">
                                    <div class="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto" data-code-file-tabs></div>
                                    <button type="button" id="code-add-file-btn" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 text-xs font-semibold text-slate-200 transition hover:bg-white/10" aria-label="Add file">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <button type="button" id="run-preview-btn" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-black text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-6.518-3.75A1 1 0 007 8.268v7.464a1 1 0 001.234.97l6.518-3.732A1 1 0 0016 14.268V9.732a1 1 0 00-1.248-.664z"/>
                                        </svg>
                                        Run Preview
                                    </button>
                                    <button type="button" id="code-save-btn" class="h-10 rounded-xl border border-white/10 bg-white/5 px-3 text-xs font-semibold text-slate-200 transition hover:bg-white/10">Save</button>
                                    <div class="relative">
                                        <button type="button" id="code-more-btn" class="h-10 rounded-xl border border-white/10 bg-white/5 px-3 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                            More
                                        </button>
                                        <div id="code-more-menu" class="absolute right-0 top-full z-30 mt-2 hidden w-52 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                            <button type="button" id="code-rename-file-btn" class="flex w-full items-center rounded-xl px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">Rename file</button>
                                            <button type="button" id="code-delete-file-btn" class="flex w-full items-center rounded-xl px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">Delete file</button>
                                            <button type="button" id="reset-code-btn" class="flex w-full items-center rounded-xl px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-950">Reset workspace</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative">
                                <div
                                    id="classroom-code-editor"
                                    class="h-[640px] w-full overflow-hidden"
                                    aria-label="Shared classroom code editor"
                                ></div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 px-4 py-3 text-xs text-slate-400">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white/5 px-2.5 py-1 font-medium text-slate-300" id="code-save-state">Ready</span>
                                    <span class="rounded-full bg-white/5 px-2.5 py-1 font-medium text-slate-300" id="code-language-label-inline">HTML</span>
                                    <span class="rounded-full bg-white/5 px-2.5 py-1 font-medium text-slate-300" id="code-cursor-status">Ln 1, Col 1</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white/5 px-2.5 py-1 font-medium text-slate-300" id="code-file-count">{{ $codeFiles->count() }} files</span>
                                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                    <span id="code-status-inline">Ready to edit</span>
                                </div>
                            </div>
                        </section>

                        <div class="space-y-3">
                            <section class="overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-100 bg-slate-50 px-3 py-3">
                                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500 shadow-sm">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-2.829 2.828a4 4 0 105.657 5.657l.707-.707m1.414-7.071a4 4 0 015.657 5.657l-2.829 2.828a4 4 0 01-5.657-5.657l.707-.707"/>
                                        </svg>
                                        <span class="truncate">https://preview.classbridge.live</span>
                                        <button type="button" id="code-preview-refresh-btn" class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" aria-label="Refresh preview">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M20 9A8 8 0 006.34 5.36L4 10m16 4-2.34 4.64A8 8 0 014 15"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="relative">
                                    <div data-preview-empty-state class="absolute inset-0 z-10 grid place-items-center bg-white px-6 text-center text-slate-500">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">Preview is ready</p>
                                            <p class="mt-1 text-xs leading-5 text-slate-500">Click Run Preview to render the current HTML, CSS, and JavaScript.</p>
                                        </div>
                                    </div>
                                    <iframe
                                        id="code-preview-frame"
                                        class="h-[380px] w-full border-0 bg-white"
                                        title="Live code preview"
                                        sandbox="allow-scripts"
                                    ></iframe>
                                </div>
                            </section>

                            <section class="overflow-hidden rounded-[1.25rem] border border-slate-900 bg-slate-950 shadow-sm">
                                <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-white">Console</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="code-console-clear-btn" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10">
                                            Clear
                                        </button>
                                        <button type="button" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-white/10" id="share-session-btn" data-copy-text="{{ $roomJoinLink }}">
                                            Share
                                        </button>
                                    </div>
                                </div>
                                <pre id="code-output" class="h-[250px] overflow-auto bg-black/20 p-4 text-xs leading-6 text-emerald-100">Preview output appears here.</pre>
                            </section>
                        </div>
                    </div>
                </section>

                <section data-workspace-panel="text" @class(['cb-ide-shell p-4 sm:p-6 space-y-5', 'hidden' => $activePanelKey !== 'text'])>
                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                        <section class="cb-card overflow-hidden">
                            <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Text Pad</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">Shared writing for essays, comprehension, and lesson notes</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                                    <span id="textpad-status" class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">Ready</span>
                                    <span id="textpad-word-count" class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">0 words</span>
                                    <button type="button" id="textpad-save-btn" class="cb-btn-secondary px-4 py-2 text-xs">Save notes</button>
                                </div>
                            </div>

                            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2" data-textpad-toolbar>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-command="bold" title="Bold"><strong>B</strong></button>
                                    <button type="button" class="cb-textpad-tool-btn italic" data-textpad-command="italic" title="Italic">I</button>
                                    <button type="button" class="cb-textpad-tool-btn underline" data-textpad-command="underline" title="Underline">U</button>
                                    <span class="cb-textpad-toolbar-divider"></span>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-block="p" title="Paragraph">P</button>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-block="h2" title="Heading">H2</button>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-block="blockquote" title="Quote">"</button>
                                    <span class="cb-textpad-toolbar-divider"></span>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-command="insertUnorderedList" title="Bullet list">List</button>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-command="insertOrderedList" title="Numbered list">1. List</button>
                                    <span class="cb-textpad-toolbar-divider"></span>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-command="justifyLeft" title="Align left">Left</button>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-command="justifyCenter" title="Align center">Center</button>
                                    <button type="button" class="cb-textpad-tool-btn" data-textpad-action="clear-formatting" title="Clear formatting">Clear</button>
                                </div>
                            </div>

                            <div class="bg-white p-4">
                                <div
                                    id="textpad-editor"
                                    contenteditable="{{ $canTypeTextPad ? 'true' : 'false' }}"
                                    data-empty="true"
                                    data-placeholder="Write lesson text, essay drafts, reading answers, or collaborative notes here..."
                                    role="textbox"
                                    aria-multiline="true"
                                    class="cb-textpad-editor min-h-[520px] rounded-[1.5rem] border border-slate-200 bg-white px-5 py-5 text-[15px] leading-8 text-slate-800 outline-none transition focus:border-sky-300 focus:shadow-[0_0_0_4px_rgba(14,165,233,0.12)]"
                                ></div>
                            </div>

                            <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/70 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white px-3 py-1.5 font-semibold text-slate-600">Shared editor</span>
                                    <span id="textpad-language-label" class="rounded-full bg-white px-3 py-1.5 font-semibold text-slate-600">English / Writing</span>
                                    <span id="textpad-collab-status" class="rounded-full bg-white px-3 py-1.5 font-semibold text-slate-600">Learners can write together</span>
                                </div>
                                <div id="textpad-selection-status" class="font-semibold text-slate-500">Cursor ready</div>
                            </div>
                        </section>

                        <aside class="space-y-4">
                            <div class="cb-card p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Teacher comments</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">Corrections and prompts</p>
                                    </div>
                                    <span id="textpad-comment-count" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">0</span>
                                </div>

                                <div id="textpad-comments-list" class="mt-4 space-y-3"></div>

                                @if ($isTeacher)
                                    <div class="mt-4 space-y-3 border-t border-slate-100 pt-4">
                                        <textarea
                                            id="textpad-comment-input"
                                            rows="3"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-sky-300 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                            placeholder="Add a correction or writing prompt..."
                                        ></textarea>
                                        <button type="button" id="textpad-comment-add-btn" class="cb-btn-primary w-full justify-center px-4 py-3 text-sm">Add teacher comment</button>
                                    </div>
                                @else
                                    <p class="mt-4 border-t border-slate-100 pt-4 text-sm leading-6 text-slate-600">Teacher feedback will appear here while you write.</p>
                                @endif
                            </div>

                            <div class="cb-card p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Live typing</p>
                                <p id="textpad-typing-status" class="mt-2 text-sm font-semibold text-slate-900">Waiting for shared writing activity.</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Use this for English, essays, corrections, reading comprehension, and collaborative notes.</p>
                            </div>
                        </aside>
                    </div>
                </section>

                <section data-workspace-panel="presentation" @class(['cb-ide-shell p-4 sm:p-6 space-y-5', 'hidden' => $activePanelKey !== 'presentation'])>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="cb-page-kicker">Presentation</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Show slides, lesson material, and annotations</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                Use uploaded materials or simple slide placeholders while keeping the pointer and notes in the same classroom.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-status-badge tone="info">Slides</x-status-badge>
                            <x-status-badge tone="neutral">Annotations</x-status-badge>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                        <section class="cb-card overflow-hidden">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Lesson material</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900">Uploaded slides or teaching files</p>
                                </div>
                                <x-status-badge tone="success">{{ $lessonResources->count() }} items</x-status-badge>
                            </div>

                            <div class="grid min-h-[480px] place-items-center bg-slate-50 p-6">
                                @if ($lessonResources->isNotEmpty())
                                    <div class="grid w-full gap-4 sm:grid-cols-2">
                                        @foreach ($lessonResources->take(4) as $resource)
                                            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Material</p>
                                                <p class="mt-2 text-base font-black text-slate-900">{{ data_get($resource, 'title', data_get($resource, 'name', 'Lesson resource')) }}</p>
                                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ data_get($resource, 'description', 'Teaching file or slide set.') }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="max-w-md text-center">
                                        <div class="mx-auto grid h-20 w-20 place-items-center rounded-[1.75rem] bg-white text-3xl shadow-sm">▢</div>
                                        <h3 class="mt-5 text-xl font-black text-slate-900">Slide preview placeholder</h3>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">Add lesson materials later and the presentation mode can show them here.</p>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <aside class="space-y-4">
                            <div class="cb-card p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Annotation</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Teacher annotations and pointer support can sit beside the slide later.</p>
                            </div>
                            <div class="cb-card p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Session notes</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Notes are saved from the side panel and remain attached to this lesson.</p>
                            </div>
                        </aside>
                    </div>
                </section>
            </div>

            <aside class="cb-ide-shell overflow-hidden p-0" x-show="rightPanelOpen" x-cloak>
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-black text-slate-900">Classroom panel</p>
                        <p class="text-xs font-medium text-slate-500">Chat, people, files, notes, controls</p>
                    </div>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" @click="rightPanelOpen = false" aria-label="Collapse classroom panel">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6l-6 6 6 6"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-5 overflow-x-auto border-b border-slate-200 px-4 pt-3">
                    @foreach (['chat' => 'Chat', 'participants' => 'Participants', 'resources' => 'Resources', 'notes' => 'Session Notes', 'permissions' => 'Permissions'] as $tabKey => $tabLabel)
                        <button
                            type="button"
                            data-classroom-tab="{{ $tabKey }}"
                            data-classroom-tab-main="true"
                            class="cb-right-rail-tab"
                        >
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>

                <div class="p-4">
                    <div data-classroom-panel="chat" class="space-y-4">
                        <div id="chat-messages" class="max-h-[520px] min-h-[360px] space-y-3 overflow-y-auto pr-1">
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">
                                Messages will appear here.
                            </div>
                        </div>

                        <div class="flex items-end gap-2 border-t border-slate-100 pt-3">
                            <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-900" aria-label="Attach file">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.44 11.05l-8.49 8.49a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                                </svg>
                            </button>
                            <textarea
                                id="chat-input"
                                rows="2"
                                class="min-h-[2.75rem] flex-1 resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                                placeholder="Type a message..."
                            ></textarea>
                            <button type="button" id="chat-send-btn" class="cb-btn-primary h-11 px-4 text-xs">Send</button>
                        </div>
                    </div>

                    <div data-classroom-panel="participants" class="hidden space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900"><span id="participant-count">{{ $participantCount }}</span> participants</p>
                                <p class="mt-1 text-xs font-medium text-slate-500">Online state and classroom roles</p>
                            </div>
                            <button type="button" data-classroom-tab-jump="participants" class="text-xs font-semibold text-sky-600 transition hover:text-sky-700">View all</button>
                        </div>

                        <div id="participants-list" class="space-y-3">
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">
                                Participants will appear here.
                            </div>
                        </div>
                    </div>

                    <div data-classroom-panel="resources" class="hidden space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">Resources</p>
                                <p class="mt-1 text-xs font-medium text-slate-500">Files and lesson materials</p>
                            </div>
                            <span id="resources-count" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $lessonResources->count() }}</span>
                        </div>

                        @if ($isTeacher)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <input id="resource-file-input" type="file" class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.txt,.doc,.docx,.ppt,.pptx">
                                <button type="button" id="resource-upload-btn" class="cb-btn-secondary mt-3 w-full justify-center px-4 py-2 text-xs">Upload resource</button>
                                <p id="resource-upload-status" class="mt-2 text-xs font-medium text-slate-500">PDFs, images, documents, and slides.</p>
                            </div>
                        @endif

                        <div id="resources-list" class="space-y-3"></div>
                    </div>

                    <div data-classroom-panel="notes" class="hidden space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">Session notes</p>
                                <p id="session-notes-status" class="mt-1 text-xs font-medium text-slate-500">Autosave ready</p>
                            </div>
                            @if ($isTeacher)
                                <button type="button" id="session-notes-save-btn" class="cb-btn-secondary px-4 py-2 text-xs">Save</button>
                            @endif
                        </div>

                        @if ($isTeacher)
                            <select id="session-notes-visibility" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200">
                                <option value="private" @selected(data_get($session?->metadata, 'session_notes_visibility', 'private') === 'private')>Private teacher notes</option>
                                <option value="shared" @selected(data_get($session?->metadata, 'session_notes_visibility', 'private') === 'shared')>Shared with learners</option>
                            </select>
                            <textarea
                                id="session-notes-editor"
                                rows="12"
                                class="min-h-[22rem] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                                placeholder="Write session notes here..."
                            >{{ $sessionNotes ?? '' }}</textarea>
                        @else
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                                @if (data_get($session?->metadata, 'session_notes_visibility') === 'shared')
                                    {{ $sessionNotes ?: 'Shared session notes will appear here.' }}
                                @else
                                    Session notes are private to the teacher.
                                @endif
                            </div>
                        @endif
                    </div>

                    <div data-classroom-panel="permissions" class="hidden space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">Permissions</p>
                                <p id="permissions-status" class="mt-1 text-xs font-medium text-slate-500">Server enforced</p>
                            </div>
                            @if ($isTeacher)
                                <button type="button" id="apply-permissions-btn" class="cb-btn-primary px-4 py-2 text-xs">Save</button>
                            @endif
                        </div>

                        <div class="space-y-3">
                            @foreach ([
                                'chat' => 'Learners can chat',
                                'code' => 'Learners can edit code',
                                'draw' => 'Learners can draw',
                                'type' => 'Learners can type',
                                'pointer' => 'Learners can use pointer',
                                'download' => 'Learners can download resources',
                                'whiteboard_draw' => 'Whiteboard drawing',
                                'whiteboard_text' => 'Whiteboard text',
                                'whiteboard_shapes' => 'Whiteboard shapes',
                                'whiteboard_images' => 'Whiteboard images',
                                'whiteboard_erase' => 'Whiteboard erasing',
                                'whiteboard_pointer' => 'Whiteboard pointer',
                                'whiteboard_comments' => 'Whiteboard comments',
                                'whiteboard_page_switch' => 'Learners can change page',
                                'whiteboard_page_create' => 'Learners can add page',
                                'whiteboard_object_move' => 'Learners can move objects',
                                'whiteboard_download' => 'Whiteboard download',
                                'whiteboard_follow_teacher_page' => 'Follow teacher page',
                                'whiteboard_follow_teacher_viewport' => 'Follow teacher viewport',
                            ] as $permissionKey => $permissionLabel)
                                <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <span>{{ $permissionLabel }}</span>
                                    @if ($isTeacher)
                                        <input
                                            type="checkbox"
                                            class="cb-toggle"
                                            data-permission-toggle
                                            data-permission-key="{{ $permissionKey }}"
                                            @checked(data_get($effectivePermissions, $permissionKey, false))
                                        >
                                    @else
                                        <span class="text-xs font-semibold text-slate-500">{{ data_get($effectivePermissions, $permissionKey, false) ? 'On' : 'Off' }}</span>
                                    @endif
                                </label>
                            @endforeach

                            @if ($isTeacher)
                                <label class="flex items-center justify-between gap-3 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                    <span>Lock room</span>
                                    <input type="checkbox" class="cb-toggle" data-permission-lock="room">
                                </label>
                                <label class="flex items-center justify-between gap-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                                    <span>Lock active workspace</span>
                                    <input type="checkbox" class="cb-toggle" data-permission-lock="workspace">
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Quick actions</p>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            <button type="button" class="cb-quick-room-action rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" data-room-action="share-screen">
                                Share link
                            </button>
                            <button type="button" class="cb-quick-room-action rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" data-room-action="mute-all">
                                Mute all
                            </button>
                            <button type="button" class="cb-quick-room-action rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white" data-room-action="lock-room">
                                Lock room
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
        <section class="mt-4 flex items-center gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            <svg class="h-5 w-5 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M9.172 9l-.707-.707A8 8 0 1119.07 16.6l-.707-.707A6 6 0 109.172 9z"/>
            </svg>
            <p>
                <span class="font-semibold">Safety notice:</span>
                Teachers and students interact only inside this classroom. No access to private desktop apps, browser history, or files.
            </p>
        </section>
    @else
        <section class="cb-ide-shell p-8 text-center">
            <div class="mx-auto max-w-2xl">
                <x-status-badge tone="warning">Lesson not started</x-status-badge>
                <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-900">Start the live session to open the classroom workspace.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">
                    This room already has a code and a join link. Start the session and the unified lesson workspace will open for teacher and learner.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    @if ($isTeacher)
                        <form method="POST" action="{{ route('classrooms.start-session', $classroom) }}">
                            @csrf
                            <x-primary-button type="submit">
                                Start live lesson
                            </x-primary-button>
                        </form>
                    @endif
                    <x-secondary-button type="button" data-copy-text="{{ $joinCode }}">Copy room code</x-secondary-button>
                    <x-secondary-button type="button" data-copy-text="{{ $roomJoinLink }}">Copy room link</x-secondary-button>
                </div>
            </div>
        </section>
    @endif
</div>
