@php
    $whiteboardState = data_get($session?->metadata, 'whiteboard_state', [
        'active_page' => 'page-1',
        'zoom' => 100,
        'viewport' => ['x' => 0, 'y' => 0],
        'pages' => [
            ['key' => 'page-1', 'name' => 'Page 1', 'sort_order' => 0],
        ],
    ]);

    $whiteboardPages = collect(data_get($whiteboardState, 'pages', []));
    $activeWhiteboardPage = data_get($whiteboardState, 'active_page', 'page-1');
@endphp

<section data-workspace-panel="whiteboard" data-whiteboard-root @class(['cb-whiteboard-shell p-4 sm:p-5 space-y-4', 'hidden' => $activePanelKey !== 'whiteboard'])>
    <div class="cb-whiteboard-header">
        <div class="min-w-0">
            <p class="cb-page-kicker">Board title</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <p data-whiteboard-board-title class="text-lg font-black tracking-tight text-slate-900 sm:text-xl">{{ $classroom->title }}</p>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Current page</span>
                <span data-whiteboard-current-page class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ data_get($whiteboardPages->firstWhere('key', $activeWhiteboardPage), 'name', 'Page 1') }}
                </span>
                <span data-whiteboard-zoom-label class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">100%</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-whiteboard-action="undo" class="cb-btn-secondary px-3 py-2 text-xs font-semibold">Undo</button>
            <button type="button" data-whiteboard-action="redo" class="cb-btn-secondary px-3 py-2 text-xs font-semibold">Redo</button>
            <button type="button" data-whiteboard-action="fit-board" class="cb-btn-secondary px-3 py-2 text-xs font-semibold">Fit to screen</button>
            <button type="button" data-whiteboard-action="fullscreen" class="cb-btn-secondary px-3 py-2 text-xs font-semibold">Fullscreen</button>
            <button type="button" data-whiteboard-action="export" class="cb-btn-secondary px-3 py-2 text-xs font-semibold">Export</button>
            <button type="button" data-whiteboard-action="toggle-more-menu" class="cb-btn-secondary px-3 py-2 text-xs font-semibold">More options</button>
        </div>
    </div>

    <div class="lg:hidden cb-whiteboard-mobile-toolbar">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="cb-page-kicker">Touch toolbar</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">Quick tools</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-whiteboard-action="fullscreen" class="cb-whiteboard-mobile-action">
                    <x-whiteboard.icon name="fit" class="h-4 w-4" />
                    <span>Fullscreen</span>
                </button>
            </div>
        </div>

        <div class="mt-3 space-y-2">
            <div class="cb-whiteboard-mobile-strip">
                <button type="button" data-whiteboard-tool="select" class="cb-whiteboard-mobile-tool" title="Select">
                    <x-whiteboard.icon name="select" class="h-5 w-5" />
                    <span>Select</span>
                </button>
                <button type="button" data-whiteboard-tool="hand" class="cb-whiteboard-mobile-tool" title="Pan">
                    <x-whiteboard.icon name="hand" class="h-5 w-5" />
                    <span>Pan</span>
                </button>
                <button type="button" data-whiteboard-tool="pen" class="cb-whiteboard-mobile-tool" title="Pen">
                    <x-whiteboard.icon name="pen" class="h-5 w-5" />
                    <span>Pen</span>
                </button>
                <button type="button" data-whiteboard-tool="highlighter" class="cb-whiteboard-mobile-tool" title="Highlighter">
                    <x-whiteboard.icon name="highlighter" class="h-5 w-5" />
                    <span>Highlight</span>
                </button>
                <button type="button" data-whiteboard-tool="eraser" class="cb-whiteboard-mobile-tool" title="Eraser">
                    <x-whiteboard.icon name="eraser" class="h-5 w-5" />
                    <span>Eraser</span>
                </button>
                <button type="button" data-whiteboard-tool="text" class="cb-whiteboard-mobile-tool" title="Text">
                    <x-whiteboard.icon name="text" class="h-5 w-5" />
                    <span>Text</span>
                </button>
                <button type="button" data-whiteboard-tool="sticky_note" class="cb-whiteboard-mobile-tool" title="Sticky note">
                    <x-whiteboard.icon name="sticky_note" class="h-5 w-5" />
                    <span>Note</span>
                </button>
                <button type="button" data-whiteboard-tool="rectangle" class="cb-whiteboard-mobile-tool" title="Rectangle">
                    <x-whiteboard.icon name="shapes" class="h-5 w-5" />
                    <span>Rect</span>
                </button>
                <button type="button" data-whiteboard-tool="circle" class="cb-whiteboard-mobile-tool" title="Circle">
                    <x-whiteboard.icon name="shapes" class="h-5 w-5" />
                    <span>Circle</span>
                </button>
                <button type="button" data-whiteboard-tool="line" class="cb-whiteboard-mobile-tool" title="Line">
                    <x-whiteboard.icon name="line" class="h-5 w-5" />
                    <span>Line</span>
                </button>
                <button type="button" data-whiteboard-tool="arrow" class="cb-whiteboard-mobile-tool" title="Arrow">
                    <x-whiteboard.icon name="arrow" class="h-5 w-5" />
                    <span>Arrow</span>
                </button>
                <button type="button" data-whiteboard-tool="image" class="cb-whiteboard-mobile-tool" title="Image">
                    <x-whiteboard.icon name="image" class="h-5 w-5" />
                    <span>Image</span>
                </button>
                <button type="button" data-whiteboard-tool="table" class="cb-whiteboard-mobile-tool" title="Table">
                    <x-whiteboard.icon name="table" class="h-5 w-5" />
                    <span>Table</span>
                </button>
                <button type="button" data-whiteboard-tool="equation" class="cb-whiteboard-mobile-tool" title="Equation">
                    <x-whiteboard.icon name="equation" class="h-5 w-5" />
                    <span>Equation</span>
                </button>
                <button type="button" data-whiteboard-tool="comment" class="cb-whiteboard-mobile-tool" title="Comment">
                    <x-whiteboard.icon name="comment" class="h-5 w-5" />
                    <span>Comment</span>
                </button>
                <button type="button" data-whiteboard-tool="templates" class="cb-whiteboard-mobile-tool" title="Templates">
                    <x-whiteboard.icon name="templates" class="h-5 w-5" />
                    <span>Templates</span>
                </button>
                <button type="button" data-whiteboard-tool="laser_pointer" class="cb-whiteboard-mobile-tool" title="Laser pointer">
                    <x-whiteboard.icon name="laser_pointer" class="h-5 w-5" />
                    <span>Laser</span>
                </button>
            </div>

            <div class="cb-whiteboard-mobile-strip cb-whiteboard-mobile-strip-actions">
                <button type="button" data-whiteboard-action="undo" class="cb-whiteboard-mobile-action">
                    <x-whiteboard.icon name="undo" class="h-4 w-4" />
                    <span>Undo</span>
                </button>
                <button type="button" data-whiteboard-action="redo" class="cb-whiteboard-mobile-action">
                    <x-whiteboard.icon name="redo" class="h-4 w-4" />
                    <span>Redo</span>
                </button>
                <button type="button" data-whiteboard-action="fit-board" class="cb-whiteboard-mobile-action">
                    <x-whiteboard.icon name="fit" class="h-4 w-4" />
                    <span>Fit</span>
                </button>
                <button type="button" data-whiteboard-action="reset-zoom" class="cb-whiteboard-mobile-action">
                    <span class="text-[11px] font-black leading-none">100%</span>
                    <span>Reset</span>
                </button>
                <button type="button" data-whiteboard-action="export" class="cb-whiteboard-mobile-action">
                    <x-whiteboard.icon name="export" class="h-4 w-4" />
                    <span>Export</span>
                </button>
            </div>
        </div>
    </div>

    <div class="cb-whiteboard-layout grid gap-4 xl:grid-cols-[84px_minmax(0,1fr)]">
        <aside class="cb-whiteboard-toolbar hidden xl:flex" aria-label="Whiteboard tools">
            <button type="button" data-whiteboard-tool="select" class="cb-whiteboard-tool" title="Select and edit objects" aria-label="Select tool">
                <x-whiteboard.icon name="select" />
            </button>
            <button type="button" data-whiteboard-tool="hand" class="cb-whiteboard-tool" title="Pan the board" aria-label="Hand tool">
                <x-whiteboard.icon name="hand" />
            </button>
            <button type="button" data-whiteboard-tool="pen" class="cb-whiteboard-tool" title="Pen" aria-label="Pen tool">
                <x-whiteboard.icon name="pen" />
            </button>
            <button type="button" data-whiteboard-tool="highlighter" class="cb-whiteboard-tool" title="Highlighter" aria-label="Highlighter tool">
                <x-whiteboard.icon name="highlighter" />
            </button>
            <button type="button" data-whiteboard-tool="eraser" class="cb-whiteboard-tool" title="Eraser" aria-label="Eraser tool">
                <x-whiteboard.icon name="eraser" />
            </button>
            <button type="button" data-whiteboard-tool="text" class="cb-whiteboard-tool" title="Text" aria-label="Text tool">
                <x-whiteboard.icon name="text" />
            </button>
            <button type="button" data-whiteboard-tool="sticky_note" class="cb-whiteboard-tool" title="Sticky note" aria-label="Sticky note tool">
                <x-whiteboard.icon name="sticky_note" />
            </button>

            <div class="relative">
                <button type="button" data-whiteboard-action="toggle-shapes-menu" class="cb-whiteboard-tool" title="Shapes" aria-label="Shapes menu">
                    <x-whiteboard.icon name="shapes" />
                </button>
                <div data-whiteboard-shapes-menu class="cb-whiteboard-popover hidden">
                    <button type="button" data-whiteboard-insert-shape="rectangle">Rectangle</button>
                    <button type="button" data-whiteboard-insert-shape="rounded_rectangle">Rounded rect</button>
                    <button type="button" data-whiteboard-insert-shape="circle">Circle</button>
                    <button type="button" data-whiteboard-insert-shape="ellipse">Ellipse</button>
                    <button type="button" data-whiteboard-insert-shape="triangle">Triangle</button>
                    <button type="button" data-whiteboard-insert-shape="diamond">Diamond</button>
                    <button type="button" data-whiteboard-insert-shape="star">Star</button>
                    <button type="button" data-whiteboard-insert-shape="speech_bubble">Speech bubble</button>
                    <button type="button" data-whiteboard-insert-shape="cloud">Cloud</button>
                </div>
            </div>

            <div class="relative">
                <button type="button" data-whiteboard-action="toggle-lines-menu" class="cb-whiteboard-tool" title="Line tools" aria-label="Line tools menu">
                    <x-whiteboard.icon name="line" />
                </button>
                <div data-whiteboard-lines-menu class="cb-whiteboard-popover hidden">
                    <button type="button" data-whiteboard-insert-line="line">Straight line</button>
                    <button type="button" data-whiteboard-insert-line="arrow">Arrow</button>
                    <button type="button" data-whiteboard-insert-line="double_arrow">Double arrow</button>
                    <button type="button" data-whiteboard-insert-line="dashed_line">Dashed line</button>
                    <button type="button" data-whiteboard-insert-line="curved_connector">Curved connector</button>
                </div>
            </div>

            <button type="button" data-whiteboard-tool="arrow" class="cb-whiteboard-tool" title="Arrow" aria-label="Arrow tool">
                <x-whiteboard.icon name="arrow" />
            </button>
            <button type="button" data-whiteboard-tool="image" class="cb-whiteboard-tool" title="Image" aria-label="Image tool">
                <x-whiteboard.icon name="image" />
            </button>
            <button type="button" data-whiteboard-tool="table" class="cb-whiteboard-tool" title="Table" aria-label="Table tool">
                <x-whiteboard.icon name="table" />
            </button>
            <button type="button" data-whiteboard-tool="equation" class="cb-whiteboard-tool" title="Equation" aria-label="Equation tool">
                <x-whiteboard.icon name="equation" />
            </button>
            <button type="button" data-whiteboard-tool="laser_pointer" class="cb-whiteboard-tool" title="Laser pointer" aria-label="Laser pointer tool">
                <x-whiteboard.icon name="laser_pointer" />
            </button>
            <button type="button" data-whiteboard-tool="comment" class="cb-whiteboard-tool" title="Comment" aria-label="Comment tool">
                <x-whiteboard.icon name="comment" />
            </button>
            <button type="button" data-whiteboard-tool="templates" class="cb-whiteboard-tool" title="Templates" aria-label="Templates tool">
                <x-whiteboard.icon name="templates" />
            </button>

            <div class="relative">
                <button type="button" data-whiteboard-action="toggle-more-menu" class="cb-whiteboard-tool" title="More tools" aria-label="More tools menu">
                    <x-whiteboard.icon name="more_tools" />
                </button>
                <div data-whiteboard-more-menu class="cb-whiteboard-popover hidden">
                    <button type="button" data-whiteboard-action="copy-selection">Copy</button>
                    <button type="button" data-whiteboard-action="paste-selection">Paste</button>
                    <button type="button" data-whiteboard-action="select-all">Select all</button>
                    <button type="button" data-whiteboard-action="group-selection">Group</button>
                    <button type="button" data-whiteboard-action="ungroup-selection">Ungroup</button>
                    <button type="button" data-whiteboard-action="duplicate-selection">Duplicate selection</button>
                    <button type="button" data-whiteboard-action="delete-selection">Delete selection</button>
                    <button type="button" data-whiteboard-action="clear-page">Clear current page</button>
                    <button type="button" data-whiteboard-action="lock-selection">Lock selection</button>
                    <button type="button" data-whiteboard-action="unlock-selection">Unlock selection</button>
                    <button type="button" data-whiteboard-action="align-left">Align left</button>
                    <button type="button" data-whiteboard-action="align-right">Align right</button>
                    <button type="button" data-whiteboard-action="align-top">Align top</button>
                    <button type="button" data-whiteboard-action="align-bottom">Align bottom</button>
                    <button type="button" data-whiteboard-action="align-center">Align centre</button>
                    <button type="button" data-whiteboard-action="align-middle">Align middle</button>
                    <button type="button" data-whiteboard-action="distribute-horizontal">Distribute horizontal</button>
                    <button type="button" data-whiteboard-action="distribute-vertical">Distribute vertical</button>
                    <button type="button" data-whiteboard-action="bring-forward">Bring forward</button>
                    <button type="button" data-whiteboard-action="send-backward">Send backward</button>
                    <button type="button" data-whiteboard-action="bring-to-front">Bring to front</button>
                    <button type="button" data-whiteboard-action="send-to-back">Send to back</button>
                    <button type="button" data-whiteboard-action="reset-zoom">Reset zoom</button>
                </div>
            </div>
        </aside>

        <div class="min-w-0 space-y-4">
            <div class="cb-whiteboard-header">
                <div class="flex flex-wrap items-center gap-2">
                    <div>
                        <p class="cb-page-kicker">Whiteboard header</p>
                        <p data-whiteboard-board-title class="mt-1 text-sm font-semibold text-slate-900">{{ $classroom->title }}</p>
                    </div>
                    <span class="hidden h-8 w-px bg-slate-200 sm:block"></span>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Current page</span>
                        <span data-whiteboard-current-page class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            {{ data_get($whiteboardPages->firstWhere('key', $activeWhiteboardPage), 'name', 'Page 1') }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span data-whiteboard-zoom-label class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">100%</span>
                    <span data-whiteboard-connection-state class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Online</span>
                    <span data-whiteboard-autosave-state class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Saved</span>
                </div>
            </div>

            <div data-whiteboard-properties class="cb-whiteboard-properties cb-whiteboard-floatbar hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Object inspector</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span data-whiteboard-object-label class="rounded-full bg-slate-950 px-3 py-1 text-xs font-semibold text-white">Object</span>
                            <span data-whiteboard-object-kind class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Shape</span>
                        </div>
                    </div>
                    <button type="button" data-whiteboard-object-lock class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Lock</button>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>X</span>
                        <input type="number" data-whiteboard-prop-field="left" class="cb-whiteboard-field" value="0">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Y</span>
                        <input type="number" data-whiteboard-prop-field="top" class="cb-whiteboard-field" value="0">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Width</span>
                        <input type="number" data-whiteboard-prop-field="width" class="cb-whiteboard-field" value="0" min="1">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Height</span>
                        <input type="number" data-whiteboard-prop-field="height" class="cb-whiteboard-field" value="0" min="1">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Rotation</span>
                        <input type="number" data-whiteboard-prop-field="angle" class="cb-whiteboard-field" value="0">
                    </label>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Stroke</span>
                        <input type="color" data-whiteboard-prop="stroke" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#0f172a">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Fill / text colour</span>
                        <input type="color" data-whiteboard-prop="fill" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#ffffff">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Stroke width</span>
                        <input type="range" data-whiteboard-prop="strokeWidth" min="1" max="40" value="4" class="w-full">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Opacity</span>
                        <input type="range" data-whiteboard-prop="opacity" min="10" max="100" value="100" class="w-full">
                    </label>
                    <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Border style</span>
                        <select data-whiteboard-prop-field="borderStyle" class="cb-whiteboard-field">
                            <option value="solid">Solid</option>
                            <option value="dashed">Dashed</option>
                            <option value="dotted">Dotted</option>
                            <option value="dashdot">Dash-dot</option>
                        </select>
                    </label>
                </div>

                <div data-whiteboard-property-section="text" class="mt-4 hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Text tools</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">Edit text without leaving the board</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-whiteboard-text-style="bold" class="cb-btn-secondary px-4 py-2 text-xs">Bold</button>
                            <button type="button" data-whiteboard-text-style="italic" class="cb-btn-secondary px-4 py-2 text-xs">Italic</button>
                            <button type="button" data-whiteboard-text-style="underline" class="cb-btn-secondary px-4 py-2 text-xs">Underline</button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Font family</span>
                            <select data-whiteboard-prop-field="fontFamily" class="cb-whiteboard-field">
                                <option value="Instrument Sans, ui-sans-serif, system-ui, sans-serif">Instrument Sans</option>
                                <option value="Inter, ui-sans-serif, system-ui, sans-serif">Inter</option>
                                <option value="Georgia, serif">Georgia</option>
                                <option value="'Courier New', monospace">Courier New</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Font size</span>
                            <input type="number" data-whiteboard-prop-field="fontSize" class="cb-whiteboard-field" value="22" min="8" max="120">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Weight</span>
                            <select data-whiteboard-prop-field="fontWeight" class="cb-whiteboard-field">
                                <option value="400">Regular</option>
                                <option value="500">Medium</option>
                                <option value="600">Semibold</option>
                                <option value="700">Bold</option>
                                <option value="800">Extra bold</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Style</span>
                            <select data-whiteboard-prop-field="fontStyle" class="cb-whiteboard-field">
                                <option value="normal">Normal</option>
                                <option value="italic">Italic</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Line height</span>
                            <input type="number" data-whiteboard-prop-field="lineHeight" class="cb-whiteboard-field" value="1.4" min="1" max="3" step="0.1">
                        </label>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="button" data-whiteboard-text-align="left" class="cb-btn-secondary px-4 py-2 text-xs">Left</button>
                        <button type="button" data-whiteboard-text-align="center" class="cb-btn-secondary px-4 py-2 text-xs">Center</button>
                        <button type="button" data-whiteboard-text-align="right" class="cb-btn-secondary px-4 py-2 text-xs">Right</button>
                    </div>

                    <textarea data-whiteboard-prop-field="text" rows="3" class="mt-4 cb-whiteboard-textarea" placeholder="Type here..."></textarea>
                </div>

                <div data-whiteboard-property-section="table" class="mt-4 hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Table editor</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">Resize and style the table in one place</p>
                        </div>
                        <button type="button" data-whiteboard-action="apply-table" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Apply table</button>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Text colour</span>
                            <input type="color" data-whiteboard-prop-field="tableTextColor" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#0f172a">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Fill colour</span>
                            <input type="color" data-whiteboard-prop-field="tableFill" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#ffffff">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Border colour</span>
                            <input type="color" data-whiteboard-prop-field="tableStroke" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#cbd5e1">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Text align</span>
                            <select data-whiteboard-prop-field="tableTextAlign" class="cb-whiteboard-field">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Rows</span>
                            <input type="number" data-whiteboard-prop-field="tableRows" class="cb-whiteboard-field" value="2" min="1" max="10">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Columns</span>
                            <input type="number" data-whiteboard-prop-field="tableColumns" class="cb-whiteboard-field" value="2" min="1" max="10">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Cell padding</span>
                            <input type="number" data-whiteboard-prop-field="tableCellPadding" class="cb-whiteboard-field" value="12" min="0" max="32">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Border width</span>
                            <input type="number" data-whiteboard-prop-field="tableBorderWidth" class="cb-whiteboard-field" value="2" min="1" max="10">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Font size</span>
                            <input type="number" data-whiteboard-prop-field="tableFontSize" class="cb-whiteboard-field" value="15" min="8" max="36">
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Font family</span>
                            <select data-whiteboard-prop-field="tableFontFamily" class="cb-whiteboard-field">
                                <option value="Instrument Sans, ui-sans-serif, system-ui, sans-serif">Instrument Sans</option>
                                <option value="Inter, ui-sans-serif, system-ui, sans-serif">Inter</option>
                                <option value="Georgia, serif">Georgia</option>
                                <option value="'Courier New', monospace">Courier New</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Cell padding</span>
                            <input type="number" data-whiteboard-prop-field="tableCellPadding" class="cb-whiteboard-field" value="12" min="0" max="32">
                        </label>
                    </div>

                    <label class="mt-4 block space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <span>Cell text</span>
                        <textarea data-whiteboard-prop-field="tableCells" rows="4" class="cb-whiteboard-textarea" placeholder="Use tabs between columns and new lines between rows."></textarea>
                    </label>
                    <p class="mt-2 text-[11px] font-medium text-slate-500">Use one row per line. Use tabs to separate columns. Edit the style above, then click Apply table.</p>
                </div>

                <div data-whiteboard-property-section="equation" class="mt-4 hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Equation editor</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">Clean math text with live styling</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold text-slate-500">Editable</span>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Font family</span>
                            <select data-whiteboard-prop-field="equationFontFamily" class="cb-whiteboard-field">
                                <option value="Georgia, serif">Georgia</option>
                                <option value="Cambria, Georgia, serif">Cambria</option>
                                <option value="Times New Roman, serif">Times New Roman</option>
                                <option value="Instrument Sans, ui-sans-serif, system-ui, sans-serif">Instrument Sans</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Font size</span>
                            <input type="number" data-whiteboard-prop-field="equationFontSize" class="cb-whiteboard-field" value="28" min="12" max="80">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Text colour</span>
                            <input type="color" data-whiteboard-prop-field="equationFill" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#6b21a8">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Background</span>
                            <input type="color" data-whiteboard-prop-field="equationBackground" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#faf5ff">
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Border colour</span>
                            <input type="color" data-whiteboard-prop-field="equationStroke" class="h-11 w-full rounded-2xl border border-slate-200 bg-white p-1" value="#a855f7">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Border width</span>
                            <input type="number" data-whiteboard-prop-field="equationBorderWidth" class="cb-whiteboard-field" value="2" min="1" max="10">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Weight</span>
                            <select data-whiteboard-prop-field="equationFontWeight" class="cb-whiteboard-field">
                                <option value="400">Regular</option>
                                <option value="500">Medium</option>
                                <option value="600">Semibold</option>
                                <option value="700">Bold</option>
                                <option value="800">Extra bold</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Style</span>
                            <select data-whiteboard-prop-field="equationFontStyle" class="cb-whiteboard-field">
                                <option value="normal">Normal</option>
                                <option value="italic">Italic</option>
                            </select>
                        </label>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Align</span>
                            <select data-whiteboard-prop-field="equationTextAlign" class="cb-whiteboard-field">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Padding X</span>
                            <input type="number" data-whiteboard-prop-field="equationPaddingX" class="cb-whiteboard-field" value="24" min="6" max="48">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Padding Y</span>
                            <input type="number" data-whiteboard-prop-field="equationPaddingY" class="cb-whiteboard-field" value="18" min="6" max="48">
                        </label>
                        <label class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Line height</span>
                            <input type="number" data-whiteboard-prop-field="equationLineHeight" class="cb-whiteboard-field" value="1.3" min="1" max="3" step="0.1">
                        </label>
                        <div class="space-y-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                            <span>Preview</span>
                            <div data-whiteboard-equation-preview class="cb-whiteboard-equation-preview bg-white">x + 3 = 7</div>
                        </div>
                    </div>

                    <textarea data-whiteboard-prop-field="equationText" rows="3" class="mt-4 cb-whiteboard-textarea" placeholder="x + 3 = 7"></textarea>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" data-whiteboard-equation-insert="^" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Superscript</button>
                        <button type="button" data-whiteboard-equation-insert="_" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Subscript</button>
                        <button type="button" data-whiteboard-equation-insert="√" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Root</button>
                        <button type="button" data-whiteboard-equation-insert="π" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Pi</button>
                        <button type="button" data-whiteboard-equation-insert="÷" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Divide</button>
                    </div>
                </div>
            </div>

            <div class="cb-whiteboard-canvas-shell overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Shared board</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Teacher and learner edit the same board inside one classroom</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span class="rounded-full bg-slate-50 px-3 py-1 font-semibold text-slate-600">Pointer, attendance, permissions, and room code stay the same</span>
                    </div>
                </div>

                <div id="canvas-container" data-whiteboard-canvas-container class="relative min-h-[720px] overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.08),_transparent_30%),linear-gradient(180deg,_#fff,_#f8fafc)]">
                    <canvas id="whiteboard-canvas" data-whiteboard-canvas class="block h-full w-full touch-none"></canvas>
                    <div id="pointers-layer" class="pointer-events-none absolute inset-0"></div>

                    <div data-whiteboard-empty-state class="absolute inset-0 hidden items-center justify-center px-8 text-center">
                        <div class="max-w-md rounded-[1.75rem] border border-dashed border-slate-200 bg-white/90 p-6 shadow-sm">
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-[1.5rem] bg-slate-950 text-2xl text-white">◌</div>
                            <h3 class="mt-4 text-xl font-black text-slate-900">Start drawing on the board</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Choose a tool, add an object, then move, resize, rotate, or edit it directly on the canvas.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cb-whiteboard-bottom-bar">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">Current zoom</span>
                    <button type="button" data-whiteboard-action="zoom-out" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Zoom out</button>
                    <span data-whiteboard-zoom-label class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">100%</span>
                    <button type="button" data-whiteboard-action="zoom-in" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Zoom in</button>
                    <button type="button" data-whiteboard-action="fit-board" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Fit board</button>
                    <button type="button" data-whiteboard-action="reset-zoom" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Reset zoom</button>
                    <button type="button" data-whiteboard-action="prev-page" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Prev page</button>
                    <button type="button" data-whiteboard-action="next-page" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Next page</button>
                    <button type="button" data-whiteboard-action="add-page" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Add page</button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span data-whiteboard-connection-state class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Online</span>
                    <span data-whiteboard-autosave-state class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Saved</span>
                </div>
            </div>
        </div>

        <aside data-whiteboard-right-panel class="cb-whiteboard-panel hidden space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="cb-page-kicker">Board panel</p>
                    <h3 class="mt-2 text-lg font-black text-slate-900">Pages, layers, templates, comments, activity</h3>
                </div>
                <button type="button" data-whiteboard-action="toggle-right-panel" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Collapse</button>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" data-whiteboard-tab="pages" class="cb-ide-tab cb-ide-tab-active text-xs">Pages</button>
                <button type="button" data-whiteboard-tab="layers" class="cb-ide-tab cb-ide-tab-inactive text-xs">Layers</button>
                <button type="button" data-whiteboard-tab="templates" class="cb-ide-tab cb-ide-tab-inactive text-xs">Templates</button>
                <button type="button" data-whiteboard-tab="comments" class="cb-ide-tab cb-ide-tab-inactive text-xs">Comments</button>
                <button type="button" data-whiteboard-tab="activity" class="cb-ide-tab cb-ide-tab-inactive text-xs">Activity</button>
            </div>

            <div class="space-y-4">
                <div data-whiteboard-tab-panel="pages" class="space-y-3">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Pages</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">Switch board pages without leaving the lesson</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-whiteboard-action="create-snapshot" class="cb-btn-secondary px-3 py-2 text-[11px] font-semibold">Snapshot</button>
                                <button type="button" data-whiteboard-action="export-all-pages" class="cb-btn-secondary px-3 py-2 text-[11px] font-semibold">Export all</button>
                                <button type="button" data-whiteboard-action="add-page" class="cb-btn-secondary px-3 py-2 text-[11px] font-semibold">Add page</button>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                            <select data-whiteboard-page-background-type class="cb-whiteboard-field text-sm">
                                <option value="plain_white">Plain white</option>
                                <option value="soft_grey">Soft grey</option>
                                <option value="dark_board">Dark board</option>
                                <option value="grid">Grid</option>
                                <option value="graph_paper">Graph paper</option>
                                <option value="ruled_paper">Ruled paper</option>
                                <option value="dotted_paper">Dotted paper</option>
                                <option value="custom_colour">Custom colour</option>
                                <option value="uploaded_background">Uploaded image</option>
                                <option value="pdf_page">PDF page</option>
                            </select>
                            <input type="text" data-whiteboard-page-background-value class="cb-whiteboard-field text-sm" placeholder="Background value or colour">
                            <button type="button" data-whiteboard-action="apply-page-background" class="cb-btn-secondary px-4 py-2 text-xs font-semibold">Apply background</button>
                        </div>

                        <div class="mt-3 space-y-2" data-whiteboard-pages-list>
                            @foreach ($whiteboardPages as $page)
                                <button
                                    type="button"
                                    data-whiteboard-page="{{ data_get($page, 'key') }}"
                                    class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left text-sm font-semibold transition {{ data_get($page, 'key') === $activeWhiteboardPage ? 'border-slate-950 bg-slate-950 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}"
                                >
                                    <span>{{ data_get($page, 'name', 'Page') }}</span>
                                    <span class="text-[11px] opacity-70">{{ data_get($page, 'sort_order', 0) + 1 }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Version history</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">Named snapshots and restore points</p>
                            </div>
                            <button type="button" data-whiteboard-action="create-snapshot" class="cb-btn-secondary px-3 py-2 text-[11px] font-semibold">New snapshot</button>
                        </div>
                        <div class="mt-3 space-y-2" data-whiteboard-snapshots-list>
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                                No snapshots yet.
                            </div>
                        </div>
                    </div>
                </div>

                <div data-whiteboard-tab-panel="layers" class="hidden space-y-3">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Layers</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Select, hide, and manage objects on the current page</p>
                        <div class="mt-3 space-y-2" data-whiteboard-layers-list>
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                                No objects yet on this page.
                            </div>
                        </div>
                    </div>
                </div>

                <div data-whiteboard-tab-panel="templates" class="hidden space-y-3">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Templates</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Quick lesson starters</p>
                        <div class="mt-3 grid gap-2">
                            <button type="button" data-whiteboard-template="lesson-frame" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700">Lesson frame</button>
                            <button type="button" data-whiteboard-template="number-line" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700">Number line</button>
                            <button type="button" data-whiteboard-template="code-card" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700">Code card</button>
                            <button type="button" data-whiteboard-template="story-map" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700">Story map</button>
                        </div>
                    </div>
                </div>

                <div data-whiteboard-tab-panel="comments" class="hidden space-y-3">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Comments</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Teacher notes and learner comments can live here later</p>
                        <div class="mt-3 space-y-2" data-whiteboard-comments-list>
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                                No comments yet.
                            </div>
                        </div>
                    </div>
                </div>

                <div data-whiteboard-tab-panel="activity" class="hidden space-y-3">
                    <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Activity</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Recent board actions</p>
                        <div class="mt-3 space-y-2" data-whiteboard-activity-list>
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                                Board is ready.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>
