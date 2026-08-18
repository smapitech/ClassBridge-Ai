import { EditorState, Compartment, StateEffect, StateField, RangeSetBuilder } from '@codemirror/state';
import {
    EditorView,
    Decoration,
    keymap,
    lineNumbers,
    highlightActiveLine,
    highlightActiveLineGutter,
    drawSelection,
    dropCursor,
    rectangularSelection,
} from '@codemirror/view';
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands';
import { autocompletion, closeBrackets, completionKeymap } from '@codemirror/autocomplete';
import { syntaxHighlighting, defaultHighlightStyle, indentOnInput, bracketMatching } from '@codemirror/language';
import { html } from '@codemirror/lang-html';
import { css } from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { php } from '@codemirror/lang-php';
import { oneDark } from '@codemirror/theme-one-dark';

function byId(id) {
    return document.getElementById(id);
}

function meta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.content ?? '';
}

function readJsonScript(id, fallback = null) {
    const node = byId(id);
    if (!node?.textContent) {
        return fallback;
    }

    try {
        return JSON.parse(node.textContent);
    } catch {
        return fallback;
    }
}

function safeText(value) {
    return String(value ?? '').trim();
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function copyText(text) {
    if (navigator.clipboard?.writeText) {
        return navigator.clipboard.writeText(String(text ?? ''));
    }

    const fallback = document.createElement('textarea');
    fallback.value = String(text ?? '');
    fallback.setAttribute('readonly', 'readonly');
    fallback.style.position = 'absolute';
    fallback.style.left = '-9999px';
    document.body.appendChild(fallback);
    fallback.select();
    document.execCommand('copy');
    document.body.removeChild(fallback);
    return Promise.resolve();
}

function formatTime(value) {
    const date = value ? new Date(value) : new Date();
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

function languageForFile(file) {
    const name = String(file?.filename ?? '').toLowerCase();
    const language = String(file?.language ?? '').toLowerCase();

    if (name.endsWith('.html') || language === 'html') {
        return html({ autoCloseTags: true, matchClosingTags: true });
    }

    if (name.endsWith('.css') || language === 'css') {
        return css();
    }

    if (name.endsWith('.js') || language === 'javascript' || language === 'js') {
        return javascript({ jsx: true, typescript: false });
    }

    if (name.endsWith('.php') || language === 'php') {
        return php();
    }

    return [];
}

function fileLanguageLabel(file) {
    const name = String(file?.filename ?? '').toLowerCase();
    if (name.endsWith('.html')) return 'HTML';
    if (name.endsWith('.css')) return 'CSS';
    if (name.endsWith('.js')) return 'JS';
    if (name.endsWith('.php')) return 'PHP';
    if (name.endsWith('.md')) return 'MD';
    return String(file?.language ?? 'text').toUpperCase();
}

function formatHtmlContent(content) {
    const template = document.createElement('template');
    template.innerHTML = String(content ?? '').trim();

    const indentUnit = '  ';
    const lines = [];
    const selfClosing = new Set(['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr']);

    const visit = (node, depth) => {
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.textContent?.replace(/\s+/g, ' ').trim();
            if (text) {
                lines.push(indentUnit.repeat(depth) + text);
            }
            return;
        }

        if (node.nodeType === Node.COMMENT_NODE) {
            const comment = node.textContent?.trim();
            if (comment) {
                lines.push(indentUnit.repeat(depth) + `<!-- ${comment} -->`);
            }
            return;
        }

        if (node.nodeType !== Node.ELEMENT_NODE) {
            return;
        }

        const tag = node.tagName.toLowerCase();
        const attrs = [...node.attributes].map((attr) => `${attr.name}="${attr.value}"`).join(' ');
        const opening = `<${tag}${attrs ? ` ${attrs}` : ''}>`;
        const closing = `</${tag}>`;

        if (selfClosing.has(tag) && node.childNodes.length === 0) {
            lines.push(indentUnit.repeat(depth) + opening.replace('>', ' />'));
            return;
        }

        lines.push(indentUnit.repeat(depth) + opening);
        node.childNodes.forEach((child) => visit(child, depth + 1));
        lines.push(indentUnit.repeat(depth) + closing);
    };

    [...template.content.childNodes].forEach((child) => visit(child, 0));

    return lines.join('\n').replace(/\n{3,}/g, '\n\n').trimEnd() + '\n';
}

function formatBracedContent(content) {
    const lines = String(content ?? '').replace(/\r\n/g, '\n').split('\n');
    const output = [];
    let indent = 0;

    lines.forEach((raw) => {
        const line = raw.trim();
        if (!line) {
            if (output.length && output[output.length - 1] !== '') {
                output.push('');
            }
            return;
        }

        const lower = line.toLowerCase();
        const shouldDedent = line.startsWith('}') || lower.startsWith('end') || lower.startsWith('else') || lower.startsWith('catch') || lower.startsWith('finally');
        if (shouldDedent) {
            indent = Math.max(indent - 1, 0);
        }

        output.push(`${'  '.repeat(indent)}${line}`);

        const openCount = (line.match(/\{/g) || []).length;
        const closeCount = (line.match(/\}/g) || []).length;
        if (openCount > closeCount) {
            indent += openCount - closeCount;
        }
    });

    return output.join('\n').replace(/\n{3,}/g, '\n\n').trimEnd() + '\n';
}

function formatCssContent(content) {
    const normalized = String(content ?? '')
        .replace(/\r\n/g, '\n')
        .replace(/\s*{\s*/g, ' {\n')
        .replace(/;\s*/g, ';\n')
        .replace(/\s*}\s*/g, '\n}\n');

    return formatBracedContent(normalized);
}

function formatFileContent(file, content) {
    const label = fileLanguageLabel(file);

    if (label === 'HTML') {
        return formatHtmlContent(content);
    }

    if (label === 'CSS') {
        return formatCssContent(content);
    }

    if (label === 'JS' || label === 'PHP') {
        return formatBracedContent(content);
    }

    return String(content ?? '')
        .replace(/\r\n/g, '\n')
        .split('\n')
        .map((line) => line.replace(/\s+$/g, ''))
        .join('\n')
        .trimEnd() + '\n';
}

const highlightEffect = StateEffect.define();

function buildHighlightDecorations(doc, highlights = []) {
    const builder = new RangeSetBuilder();

    highlights.forEach((highlight) => {
        const start = Math.max(1, Number(highlight.line_start ?? 1));
        const end = Math.max(start, Number(highlight.line_end ?? start));

        for (let lineNumber = start; lineNumber <= end; lineNumber += 1) {
            if (lineNumber > doc.lines) {
                break;
            }

            const line = doc.line(lineNumber);
            builder.add(line.from, line.from, Decoration.line({ class: 'cb-code-highlight-line' }));
        }
    });

    return builder.finish();
}

const highlightField = StateField.define({
    create() {
        return Decoration.none;
    },
    update(value, transaction) {
        let next = value.map(transaction.changes);

        transaction.effects.forEach((effect) => {
            if (effect.is(highlightEffect)) {
                next = effect.value;
            }
        });

        return next;
    },
    provide: (field) => EditorView.decorations.from(field),
});

function createLightTheme() {
    return EditorView.theme({
        '&': {
            height: '100%',
            backgroundColor: '#ffffff',
            color: '#0f172a',
        },
        '.cm-scroller': {
            fontFamily: '"Instrument Sans", ui-sans-serif, system-ui, sans-serif',
            lineHeight: '1.7',
        },
        '.cm-content, .cm-gutter': {
            minHeight: '100%',
        },
        '.cm-content': {
            padding: '16px 0 24px',
        },
        '.cm-gutters': {
            borderRight: '1px solid rgba(148, 163, 184, 0.2)',
            backgroundColor: 'rgba(248, 250, 252, 0.95)',
            color: '#64748b',
        },
        '.cm-activeLine': {
            backgroundColor: 'rgba(148, 163, 184, 0.12)',
        },
        '.cm-activeLineGutter': {
            backgroundColor: 'rgba(15, 23, 42, 0.05)',
        },
        '.cm-selectionBackground, .cm-content ::selection': {
            backgroundColor: 'rgba(14, 165, 233, 0.28)',
        },
        '&.cm-focused .cm-cursor, .cm-cursor': {
            borderLeftColor: '#0f172a',
        },
        '.cm-tooltip': {
            border: '1px solid rgba(226, 232, 240, 0.9)',
            backgroundColor: '#fff',
            color: '#0f172a',
            borderRadius: '16px',
        },
    }, { dark: false });
}

function createDarkTheme() {
    return oneDark;
}

function initCodingStudio() {
    const sessionId = meta('coding-session-id');
    const data = readJsonScript('coding-session-data', null);

    if (!sessionId || !data?.session?.id) {
        return;
    }

    const dom = {
        root: document.querySelector('[data-coding-studio]'),
        editor: byId('coding-editor'),
        fileTabs: [...document.querySelectorAll('[data-coding-file-tab]')],
        fileButtons: [...document.querySelectorAll('[data-coding-file-key]')],
        participantList: byId('coding-participants'),
        lessonSteps: byId('coding-lesson-steps'),
        chatMessages: byId('coding-chat-messages'),
        chatInput: byId('coding-chat-input'),
        chatSend: byId('coding-chat-send'),
        chatNote: byId('coding-chat-note'),
        activeFileLabel: byId('coding-active-file-label'),
        cursorLabel: byId('coding-cursor-label'),
        typingStatus: byId('coding-typing-status'),
        saveLabel: byId('coding-save-label'),
        syncBadge: byId('coding-sync-badge'),
        syncStatus: byId('coding-sync-status'),
        sessionStatusText: byId('coding-session-status-text'),
        previewFrame: byId('coding-preview-frame'),
        consolePanel: byId('coding-console'),
        errorsPanel: byId('coding-errors'),
        testsPanel: byId('coding-tests'),
        outputTabs: [...document.querySelectorAll('[data-coding-output-tab]')],
        outputPanels: [...document.querySelectorAll('[data-coding-output-panel]')],
        mobileTabs: [...document.querySelectorAll('[data-coding-mobile-tab]')],
        mobilePanels: [...document.querySelectorAll('[data-coding-mobile-panel]')],
        runBtn: byId('run-code-btn'),
        saveBtn: byId('save-code-btn'),
        resetBtn: byId('reset-code-btn'),
        formatBtn: byId('format-code-btn'),
        submitBtn: byId('submit-work-btn'),
        shareBtn: byId('share-session-btn'),
        inviteBtn: byId('invite-session-btn'),
        themeToggle: byId('theme-toggle'),
        takeControlBtn: byId('take-control-btn'),
        releaseControlBtn: byId('release-control-btn'),
        highlightBtn: byId('highlight-selection-btn'),
        requestHelpBtn: byId('request-help-btn'),
        raiseHandBtn: byId('raise-hand-btn'),
        saveSessionBtn: byId('save-session-btn'),
        addLessonStepBtn: byId('add-lesson-step-btn'),
        addFileBtn: byId('add-file-btn'),
        permissionToggles: [...document.querySelectorAll('[data-coding-permission-toggle]')],
    };

    if (!dom.editor) {
        return;
    }

    const themeCompartment = new Compartment();
    const languageCompartment = new Compartment();
    const editableCompartment = new Compartment();

    const initialTheme = localStorage.getItem('cb-coding-theme') || 'dark';
    const initialFiles = (data.files || []).map((file) => ({ ...file }));
    const initialFileMap = new Map(initialFiles.map((file) => [file.filename, { ...file }]));
    const initialOrder = [...initialFiles].sort((left, right) => (left.sort_order ?? 0) - (right.sort_order ?? 0)).map((file) => file.filename);

    const state = {
        sessionId: Number(sessionId),
        userId: Number(data.current_user?.id ?? meta('coding-user-id') ?? 0),
        isTeacher: Boolean(data.current_user?.is_teacher),
        permissions: { ...(data.permissions || {}) },
        files: initialFiles,
        originalFiles: initialFileMap,
        fileOrder: initialOrder,
        activeFileKey: data.session?.active_file_key || initialOrder[0] || 'index.html',
        lessonSteps: [...(data.lesson_steps || [])],
        participants: [...(data.participants || [])],
        messages: [...(data.messages || [])],
        session: data.session || {},
        controllerId: data.session?.metadata?.editor_controller_id || null,
        theme: initialTheme,
        joinLink: data.session?.join_link || meta('coding-join-link') || window.location.href,
        joinCode: data.session?.join_code || meta('coding-join-code') || '',
        outputTab: 'preview',
        mobileTab: 'editor',
        dirty: false,
        saveTimer: null,
        cursorTimer: null,
        previewTimer: null,
        applyingRemote: false,
        lastPreviewHadError: false,
        lastSelection: { line: 1, column: 1 },
        lastSavedContent: '',
        connectionState: navigator.onLine ? 'connecting' : 'offline',
        echoConnected: false,
        highlights: [],
        highlightKeys: new Set(),
        events: [...(data.events || [])],
    };

    let editorView = null;
    let lastBroadcastCursorAt = 0;

    function currentFile() {
        return state.files.find((file) => file.filename === state.activeFileKey) || state.files[0] || null;
    }

    function canEdit() {
        if (state.isTeacher) {
            return true;
        }

        if (state.controllerId && Number(state.controllerId) !== state.userId) {
            return false;
        }

        return Boolean(state.permissions.edit ?? state.permissions.code ?? false);
    }

    function canChat() {
        return state.isTeacher || Boolean(state.permissions.chat ?? false);
    }

    function canPointer() {
        return state.isTeacher || Boolean(state.permissions.pointer ?? false);
    }

    function setSaveLabel(text, tone = 'neutral') {
        if (!dom.saveLabel) {
            return;
        }

        dom.saveLabel.textContent = text;
        dom.saveLabel.className = `rounded-full px-3 py-1 text-xs font-semibold ${
            tone === 'success'
                ? 'bg-emerald-50 text-emerald-700'
                : tone === 'danger'
                    ? 'bg-rose-50 text-rose-700'
                    : tone === 'warning'
                        ? 'bg-amber-50 text-amber-700'
                        : 'bg-slate-100 text-slate-600'
        }`;
    }

    function setTypingStatus(text) {
        if (dom.typingStatus) {
            dom.typingStatus.textContent = text;
        }
    }

    function setConnectionState(stateName) {
        state.connectionState = stateName;

        const map = {
            online: { label: 'Online', tone: 'success' },
            reconnecting: { label: 'Reconnecting', tone: 'warning' },
            offline: { label: 'Offline', tone: 'danger' },
            connecting: { label: 'Connecting', tone: 'info' },
        };

        const current = map[stateName] || map.connecting;

        if (dom.syncBadge) {
            dom.syncBadge.textContent = current.label;
        }

        if (dom.syncStatus) {
            dom.syncStatus.textContent = current.label;
        }

        if (dom.sessionStatusText) {
            dom.sessionStatusText.textContent = stateName === 'online'
                ? 'Teacher and student actions are syncing live inside the protected coding room.'
                : stateName === 'offline'
                    ? 'Realtime sync is offline. The studio will keep local changes and reconnect automatically.'
                    : 'Realtime sync is trying to reconnect without losing your work.';
        }
    }

    function updateEditorEditable() {
        const shouldEdit = canEdit();

        if (!editorView) {
            return;
        }

        editorView.dispatch({
            effects: editableCompartment.reconfigure(EditorView.editable.of(shouldEdit)),
        });
    }

    function updateTheme(theme) {
        state.theme = theme;
        localStorage.setItem('cb-coding-theme', theme);

        if (editorView) {
            editorView.dispatch({
                effects: themeCompartment.reconfigure(theme === 'dark' ? createDarkTheme() : createLightTheme()),
            });
        }

        if (dom.themeToggle) {
            dom.themeToggle.textContent = theme === 'dark' ? 'Light Theme' : 'Dark Theme';
        }
    }

    function highlightKey(payload) {
        return [
            payload.event_id || payload.id || '',
            payload.file_id || '',
            payload.line_start || '',
            payload.line_end || '',
            payload.note || '',
            payload.user_id || '',
        ].join(':');
    }

    function rememberHighlight(payload, { announce = false } = {}) {
        if (!payload || !payload.line_start || !payload.line_end) {
            return;
        }

        const key = highlightKey(payload);
        if (state.highlightKeys.has(key)) {
            return;
        }

        state.highlightKeys.add(key);
        state.highlights.push({ ...payload, key });

        if (announce) {
            const start = Number(payload.line_start);
            const end = Number(payload.line_end);
            const note = payload.note ? `: ${payload.note}` : '';
            appendSystemMessage(`${payload.user_name || 'Teacher'} highlighted lines ${start}-${end}${note}.`);
        }

        renderHighlights();
    }

    function renderHighlights() {
        if (!editorView) {
            return;
        }

        const file = currentFile();
        const fileId = Number(file?.id ?? 0);
        const highlights = state.highlights.filter((highlight) => Number(highlight.file_id ?? 0) === fileId);

        editorView.dispatch({
            effects: highlightEffect.of(buildHighlightDecorations(editorView.state.doc, highlights)),
        });
    }

    function syncFileButtons() {
        dom.fileTabs.forEach((button) => {
            const active = button.dataset.codingFileTab === state.activeFileKey;
            button.classList.toggle('cb-ide-tab-active', active);
            button.classList.toggle('cb-ide-tab-inactive', !active);
        });

        dom.fileButtons.forEach((button) => {
            const active = button.dataset.codingFileKey === state.activeFileKey;
            button.classList.toggle('border-slate-950', active);
            button.classList.toggle('bg-slate-950', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('shadow-lg', active);
            button.classList.toggle('shadow-slate-950/10', active);
            button.classList.toggle('border-slate-200', !active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-slate-700', !active);
        });
    }

    function syncLessonSteps() {
        if (!dom.lessonSteps) {
            return;
        }

        dom.lessonSteps.innerHTML = '';

        if (!state.lessonSteps.length) {
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500';
            empty.textContent = 'No lesson steps yet.';
            dom.lessonSteps.appendChild(empty);
            return;
        }

        state.lessonSteps.forEach((step, index) => {
            const card = document.createElement('div');
            card.className = 'rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4';

            const head = document.createElement('div');
            head.className = 'flex items-center justify-between gap-3';

            const badge = document.createElement('span');
            badge.className = 'cb-badge bg-white text-slate-500';
            badge.textContent = `Step ${index + 1}`;

            const done = document.createElement('span');
            done.className = `rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] ${step.is_done ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`;
            done.textContent = step.is_done ? 'Done' : 'Open';

            head.append(badge, done);

            const title = document.createElement('p');
            title.className = 'mt-3 text-sm font-semibold text-slate-900';
            title.textContent = step.title || 'Lesson step';

            const description = document.createElement('p');
            description.className = 'mt-1 text-sm leading-6 text-slate-600';
            description.textContent = step.description || '';

            card.append(head, title, description);
            dom.lessonSteps.appendChild(card);
        });
    }

    function syncParticipants() {
        if (!dom.participantList) {
            return;
        }

        dom.participantList.innerHTML = '';

        const active = [...state.participants].filter((participant) => participant.is_active);

        if (!active.length) {
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500';
            empty.textContent = 'No participants are in the coding room yet.';
            dom.participantList.appendChild(empty);
            return;
        }

        active.forEach((participant) => {
            const row = document.createElement('div');
            row.className = 'rounded-2xl border border-slate-200 bg-white px-4 py-3';

            const top = document.createElement('div');
            top.className = 'flex items-start justify-between gap-3';

            const left = document.createElement('div');
            left.className = 'min-w-0';

            const name = document.createElement('p');
            name.className = 'truncate text-sm font-semibold text-slate-900';
            name.textContent = participant.name || 'Participant';

            const metaLine = document.createElement('p');
            metaLine.className = 'mt-1 text-xs text-slate-500';
            metaLine.textContent = participant.role === 'teacher' ? 'Teacher / Tutor' : (participant.role === 'observer' ? 'Observer' : 'Learner');

            left.append(name, metaLine);

            const status = document.createElement('span');
            status.className = `rounded-full px-3 py-1 text-[11px] font-semibold ${participant.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`;
            status.textContent = participant.is_active ? 'Active' : 'Away';

            top.append(left, status);
            row.append(top);

            if (participant.typing_status || participant.cursor_line) {
                const detail = document.createElement('p');
                detail.className = 'mt-2 text-[11px] font-semibold text-sky-600';
                const cursorText = participant.cursor_line ? ` line ${participant.cursor_line}${participant.cursor_column ? `, col ${participant.cursor_column}` : ''}` : '';
                detail.textContent = `${participant.typing_status ? participant.typing_status.replaceAll('_', ' ') : 'ready'}${cursorText}`;
                row.append(detail);
            }

            if (participant.permissions) {
                const wrap = document.createElement('div');
                wrap.className = 'mt-3 flex flex-wrap gap-1.5';
                ['edit', 'chat', 'pointer', 'code'].forEach((key) => {
                    const chip = document.createElement('span');
                    chip.className = `rounded-full px-2 py-1 text-[10px] font-semibold ${participant.permissions[key] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`;
                    chip.textContent = key;
                    wrap.appendChild(chip);
                });
                row.appendChild(wrap);
            }

            dom.participantList.appendChild(row);
        });
    }

    function syncChatMessages() {
        if (!dom.chatMessages) {
            return;
        }

        dom.chatMessages.innerHTML = '';

        if (!state.messages.length) {
            const empty = document.createElement('div');
            empty.className = 'rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-500';
            empty.textContent = 'Chat messages will appear here during the lesson.';
            dom.chatMessages.appendChild(empty);
            return;
        }

        state.messages.forEach((message) => appendChatMessage(message));
    }

    function renderFileNavigator() {
        syncFileButtons();
        if (dom.activeFileLabel) {
            dom.activeFileLabel.textContent = state.activeFileKey;
        }
    }

    function syncEditorStatus(file = currentFile()) {
        if (dom.activeFileLabel && file) {
            dom.activeFileLabel.textContent = file.filename;
        }

        if (dom.cursorLabel) {
            dom.cursorLabel.textContent = `line ${state.lastSelection.line}, col ${state.lastSelection.column}`;
        }
    }

    function hydrateHighlightsFromEvents(events = []) {
        state.highlights = [];
        state.highlightKeys = new Set();

        events
            .filter((event) => event?.event_type === 'line.highlighted' && event?.payload)
            .forEach((event) => {
                rememberHighlight({
                    ...event.payload,
                    event_id: event.id ?? event.event_id,
                    user_id: event.user_id ?? event.payload.user_id,
                    user_name: event.user_name ?? event.payload.user_name,
                });
            });

        renderHighlights();
    }

    function renderOutputTab(tab) {
        state.outputTab = tab;

        dom.outputTabs.forEach((button) => {
            const active = button.dataset.codingOutputTab === tab;
            button.classList.toggle('cb-ide-tab-active', active);
            button.classList.toggle('cb-ide-tab-inactive', !active);
        });

        dom.outputPanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.codingOutputPanel !== tab);
        });
    }

    function renderMobileTab(tab) {
        state.mobileTab = tab;
        const mobile = window.innerWidth < 768;

        dom.mobileTabs.forEach((button) => {
            const active = button.dataset.codingMobileTab === tab;
            button.classList.toggle('bg-slate-950', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-slate-950', active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-slate-600', !active);
        });

        dom.mobilePanels.forEach((panel) => {
            if (!mobile) {
                panel.classList.remove('hidden');
                return;
            }

            panel.classList.toggle('hidden', panel.dataset.codingMobilePanel !== tab);
        });
    }

    function updateFileInState(fileKey, updater) {
        const index = state.files.findIndex((file) => file.filename === fileKey);
        if (index === -1) {
            return null;
        }

        const file = { ...state.files[index] };
        updater(file);
        state.files[index] = file;
        state.originalFiles.set(file.filename, { ...file });
        return file;
    }

    function setActiveFile(fileKey, { preserveDoc = false, remote = false } = {}) {
        const file = state.files.find((entry) => entry.filename === fileKey);
        if (!file) {
            return;
        }

        state.activeFileKey = fileKey;
        syncEditorStatus(file);
        renderFileNavigator();

        if (!editorView || preserveDoc) {
            return;
        }

        state.applyingRemote = true;
        editorView.dispatch({
            changes: {
                from: 0,
                to: editorView.state.doc.length,
                insert: file.content ?? '',
            },
            effects: [
                languageCompartment.reconfigure(languageForFile(file)),
                editableCompartment.reconfigure(EditorView.editable.of(canEdit())),
            ],
        });
        state.applyingRemote = false;
        state.dirty = false;
        setSaveLabel(remote ? 'Synced' : 'Saved', 'success');
        updateEditorEditable();
        renderHighlights();
        runPreviewSoon();
    }

    function markSelection() {
        if (!editorView) {
            return;
        }

        const selection = editorView.state.selection.main;
        const file = currentFile();
        const line = editorView.state.doc.lineAt(selection.head);
        state.lastSelection = {
            line: line.number,
            column: selection.head - line.from + 1,
        };

        if (dom.cursorLabel) {
            dom.cursorLabel.textContent = `line ${state.lastSelection.line}, col ${state.lastSelection.column}`;
        }

        const now = Date.now();
        if (now - lastBroadcastCursorAt < 350) {
            return;
        }
        lastBroadcastCursorAt = now;

        if (!canPointer()) {
            return;
        }

        fetch(`/api/coding-sessions/${state.sessionId}/cursor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                cursor_line: state.lastSelection.line,
                cursor_column: state.lastSelection.column,
                active_file_key: file?.filename || state.activeFileKey,
                typing_status: state.dirty ? 'typing' : 'idle',
            }),
        }).catch(() => {});
    }

    function scheduleSave(statusText = 'Saving...') {
        clearTimeout(state.saveTimer);
        setSaveLabel(statusText, 'warning');

        state.saveTimer = setTimeout(() => {
            persistActiveFile('auto').catch(() => {
                setSaveLabel('Save failed', 'danger');
            });
        }, 900);
    }

    function scheduleCursorPing() {
        clearTimeout(state.cursorTimer);
        state.cursorTimer = setTimeout(() => markSelection(), 250);
    }

    function schedulePreview() {
        clearTimeout(state.previewTimer);
        state.previewTimer = setTimeout(() => runCode(), 600);
    }

    function updateFileContentFromEditor() {
        const file = currentFile();
        if (!file || !editorView) {
            return;
        }

        const content = editorView.state.doc.toString();
        updateFileInState(file.filename, (draft) => {
            draft.content = content;
        });
        state.dirty = true;
        setSaveLabel('Unsaved changes', 'warning');
        setTypingStatus(`Typing in ${file.filename}`);
        scheduleSave();
        scheduleCursorPing();
        schedulePreview();
    }

    async function persistActiveFile(mode = 'manual') {
        const file = currentFile();
        if (!file || !canEdit()) {
            return false;
        }

        const response = await fetch(`/api/coding-sessions/${state.sessionId}/files/${file.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                content: file.content ?? (editorView ? editorView.state.doc.toString() : ''),
            }),
        });

        if (!response.ok) {
            throw new Error(`Save failed with status ${response.status}`);
        }

        const data = await response.json();
        state.dirty = false;
        setSaveLabel(mode === 'auto' ? 'Auto-saved' : 'Saved', 'success');
        if (data.saved_at && dom.sessionStatusText) {
            dom.sessionStatusText.textContent = `Last saved ${formatTime(data.saved_at)}.`;
        }
        return true;
    }

    async function persistAllFiles() {
        for (const file of state.files) {
            await fetch(`/api/coding-sessions/${state.sessionId}/files/${file.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': meta('csrf-token'),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ content: file.content ?? '' }),
            });
        }

        setSaveLabel('Saved', 'success');
        state.dirty = false;
    }

    function appendConsoleLine(level, values) {
        if (!dom.consolePanel) {
            return;
        }

        const line = document.createElement('div');
        line.className = `mb-2 rounded-xl px-3 py-2 ${
            level === 'error'
                ? 'bg-rose-500/10 text-rose-200'
                : level === 'warn'
                    ? 'bg-amber-500/10 text-amber-100'
                    : 'bg-white/5 text-slate-100'
        }`;
        line.textContent = `[${level}] ${values.join(' ')}`;
        dom.consolePanel.appendChild(line);
        dom.consolePanel.scrollTop = dom.consolePanel.scrollHeight;
    }

    function appendErrorLine(message) {
        if (!dom.errorsPanel) {
            return;
        }

        const line = document.createElement('div');
        line.className = 'mb-2 rounded-2xl border border-rose-200 bg-white px-4 py-3';
        line.innerHTML = `<p class="text-sm font-semibold text-rose-700">Error</p><p class="mt-1 text-sm leading-6 text-rose-600">${escapeHtml(message)}</p>`;
        dom.errorsPanel.appendChild(line);
        dom.errorsPanel.scrollTop = dom.errorsPanel.scrollHeight;
        state.lastPreviewHadError = true;
        renderOutputTab('errors');
    }

    function clearOutputPanels() {
        if (dom.consolePanel) {
            dom.consolePanel.innerHTML = '';
        }
        if (dom.errorsPanel) {
            dom.errorsPanel.innerHTML = '';
        }
        if (dom.testsPanel) {
            dom.testsPanel.innerHTML = '<p class="font-semibold">Friendly test results</p><p class="mt-2">Run the code to see a simple pass/fail summary here.</p>';
        }
        state.lastPreviewHadError = false;
    }

    function buildPreviewMarkup() {
        const htmlFile = state.files.find((file) => file.filename === 'index.html' || file.language === 'html');
        const cssFile = state.files.find((file) => file.filename === 'style.css' || file.language === 'css');
        const jsFile = state.files.find((file) => file.filename === 'script.js' || file.language === 'javascript' || file.language === 'js');
        const phpFile = state.files.find((file) => file.filename.endsWith('.php') || file.language === 'php');

        if (phpFile && !htmlFile) {
            return {
                mode: 'php',
                content: `<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>PHP preview placeholder</title>
    <style>
        body { font-family: Inter, system-ui, sans-serif; background: #f8fafc; color: #0f172a; padding: 24px; }
        .card { max-width: 720px; margin: 0 auto; background: white; border-radius: 24px; padding: 24px; box-shadow: 0 20px 50px rgba(15,23,42,.08); }
        pre { overflow: auto; background: #0f172a; color: #e2e8f0; padding: 16px; border-radius: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>PHP preview placeholder</h1>
        <p>The browser cannot execute PHP directly. The teacher can still guide the lesson inside the protected coding studio.</p>
        <pre>${escapeHtml(phpFile.content || '')}</pre>
    </div>
</body>
</html>`,
            };
        }

        const htmlContent = htmlFile?.content || '<div class="cb-preview-empty">Start typing HTML to see the output.</div>';
        const cssContent = cssFile?.content || '';
        const jsContent = jsFile?.content || '';

        const consoleProxy = `
            const send = (type, payload) => parent.postMessage({ source: 'cb-preview', type, payload }, '*');
            ['log', 'info', 'warn', 'error'].forEach((level) => {
                const original = console[level];
                console[level] = (...args) => {
                    send('console', { level, args: args.map((value) => {
                        try { return typeof value === 'string' ? value : JSON.stringify(value); }
                        catch { return String(value); }
                    }) });
                    original.apply(console, args);
                };
            });
            window.addEventListener('error', (event) => {
                send('error', { message: event.message, line: event.lineno, column: event.colno });
            });
            window.addEventListener('unhandledrejection', (event) => {
                const reason = event.reason?.message || event.reason || 'Unhandled promise rejection';
                send('error', { message: reason });
            });
        `;

        const safeJs = jsContent.replaceAll('</script>', '<\\/script>');
        const safeProxy = consoleProxy.replaceAll('</script>', '<\\/script>');

        return {
            mode: 'web',
            content: `<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        ${cssContent}
    </style>
</head>
<body>
    ${htmlContent}
    <script>
        ${safeProxy}
    <\/script>
    <script>
        try {
            ${safeJs}
        } catch (error) {
            console.error(error);
        }
    <\/script>
</body>
</html>`,
        };
    }

    function runCode() {
        const preview = buildPreviewMarkup();
        clearOutputPanels();
        renderOutputTab('preview');

        if (!dom.previewFrame) {
            return;
        }

        dom.previewFrame.srcdoc = preview.content;

        if (preview.mode === 'php') {
            if (dom.testsPanel) {
                dom.testsPanel.innerHTML = '<p class="font-semibold">PHP preview placeholder</p><p class="mt-2">PHP runs on the server. The browser is showing the code safely as a lesson placeholder.</p>';
            }
            return;
        }

        state.lastPreviewHadError = false;
        if (dom.testsPanel) {
            dom.testsPanel.innerHTML = '<p class="font-semibold">Running friendly checks...</p><p class="mt-2">The preview is live. Watch the console and errors panel for feedback.</p>';
        }
    }

    function runPreview() {
        runCode();
    }

    function runPreviewSoon() {
        clearTimeout(state.previewTimer);
        state.previewTimer = setTimeout(() => runCode(), 500);
    }

    async function formatActiveFile() {
        const file = currentFile();
        if (!file || !editorView || !canEdit()) {
            return;
        }

        const original = editorView.state.doc.toString();
        const formatted = formatFileContent(file, original);

        if (formatted === original) {
            setSaveLabel('Already formatted', 'info');
            return;
        }

        editorView.dispatch({
            changes: {
                from: 0,
                to: editorView.state.doc.length,
                insert: formatted,
            },
        });

        setSaveLabel('Formatted', 'success');
        setTypingStatus(`Formatted ${file.filename}`);
    }

    function replaceEditorDoc(content, language, { preserveSelection = true } = {}) {
        if (!editorView) {
            return;
        }

        state.applyingRemote = true;
        editorView.dispatch({
            changes: { from: 0, to: editorView.state.doc.length, insert: content ?? '' },
            effects: [
                languageCompartment.reconfigure(languageForFile({ filename: state.activeFileKey, language })),
                editableCompartment.reconfigure(EditorView.editable.of(canEdit())),
            ],
            selection: preserveSelection ? editorView.state.selection : undefined,
        });
        state.applyingRemote = false;
    }

    function persistActiveFileContent(content) {
        updateFileInState(state.activeFileKey, (file) => {
            file.content = content;
        });
    }

    function syncActiveFile(fileKey, { remote = false } = {}) {
        const file = state.files.find((item) => item.filename === fileKey);
        if (!file) {
            return;
        }

        state.activeFileKey = fileKey;
        syncEditorStatus(file);
        renderFileNavigator();

        if (editorView) {
            replaceEditorDoc(file.content || '', file.language);
        }

        renderHighlights();

        if (!remote) {
            fetch(`/api/coding-sessions/${state.sessionId}/files/active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': meta('csrf-token'),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ active_file_key: fileKey }),
            }).catch(() => {});
        }

        runPreviewSoon();
    }

    function applyStatePayload(payload) {
        if (!payload) {
            return;
        }

        state.session = { ...state.session, ...(payload.session || {}) };
        state.permissions = { ...(payload.permissions || state.permissions) };
        state.controllerId = payload.session?.metadata?.editor_controller_id ?? state.controllerId;
        state.joinLink = payload.session?.join_link || state.joinLink;
        state.joinCode = payload.session?.join_code || state.joinCode;
        state.files = (payload.files || []).map((file) => ({ ...file }));
        state.originalFiles = new Map(state.files.map((file) => [file.filename, { ...file }]));
        state.fileOrder = [...state.files].sort((left, right) => (left.sort_order ?? 0) - (right.sort_order ?? 0)).map((file) => file.filename);
        state.lessonSteps = [...(payload.lesson_steps || state.lessonSteps)];
        state.participants = [...(payload.participants || state.participants)];
        state.messages = [...(payload.messages || state.messages)];
        state.events = [...(payload.events || state.events)];
        state.activeFileKey = payload.active_file_key || state.activeFileKey || state.fileOrder[0];
        state.dirty = false;

        if (payload.session?.status) {
            setConnectionState(payload.session.status === 'live' ? 'online' : (payload.session.status === 'ended' ? 'offline' : 'connecting'));
        }

        syncFileButtons();
        syncLessonSteps();
        syncParticipants();
        syncChatMessages();
        hydrateHighlightsFromEvents(state.events);
        renderFileNavigator();
        syncEditorStatus(currentFile());
        updateEditorEditable();

        const active = currentFile();
        if (active && editorView) {
            replaceEditorDoc(active.content || '', active.language);
        }
    }

    async function loadState() {
        try {
            const response = await fetch(`/api/coding-sessions/${state.sessionId}/state`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`Unable to load session state (${response.status})`);
            }

            const payload = await response.json();
            applyStatePayload(payload);
            setConnectionState('online');
        } catch (error) {
            setConnectionState(navigator.onLine ? 'reconnecting' : 'offline');
            setTypingStatus('Waiting for live sync');
            console.warn(error);
        }
    }

    function appendChatMessage(message, { scroll = true } = {}) {
        if (!dom.chatMessages || !message) {
            return;
        }

        const mine = Number(message.user_id) === Number(state.userId);
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${mine ? 'justify-end' : 'justify-start'}`;

        const bubble = document.createElement('div');
        bubble.className = `max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm ${
            mine ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-800'
        }`;

        const metaRow = document.createElement('div');
        metaRow.className = `flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] ${
            mine ? 'text-slate-300' : 'text-slate-500'
        }`;

        const sender = document.createElement('span');
        sender.textContent = mine ? 'You' : (message.user_name || 'Participant');

        const clock = document.createElement('span');
        clock.textContent = formatTime(message.created_at);

        metaRow.append(sender, clock);

        const body = document.createElement('p');
        body.className = 'mt-2 whitespace-pre-wrap leading-6';
        body.textContent = message.message || '';

        bubble.append(metaRow, body);
        wrapper.appendChild(bubble);
        dom.chatMessages.appendChild(wrapper);

        if (scroll) {
            dom.chatMessages.scrollTop = dom.chatMessages.scrollHeight;
        }
    }

    function recordIncomingMessage(message) {
        if (!message || state.messages.some((item) => Number(item.id) === Number(message.id))) {
            return;
        }

        state.messages.push(message);
        appendChatMessage(message);
    }

    async function sendChatMessage(messageType = 'text') {
        if (!dom.chatInput || !canChat()) {
            return;
        }

        const text = safeText(dom.chatInput.value);
        if (!text) {
            return;
        }

        dom.chatInput.value = '';

        const response = await fetch(`/api/coding-sessions/${state.sessionId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                message: text,
                message_type: messageType,
            }),
        });

        if (!response.ok) {
            throw new Error(`Chat failed with status ${response.status}`);
        }

        const data = await response.json();
        if (data.message) {
            appendChatMessage({
                ...data.message,
                user_name: data.message.user?.display_name || data.message.user?.name || data.message.metadata?.display_name || 'You',
            });
        }
    }

    async function addLessonStep() {
        const title = window.prompt('Lesson step title', `Step ${state.lessonSteps.length + 1}`);
        if (!title) {
            return;
        }

        const description = window.prompt('Lesson step description', '');
        state.lessonSteps = [
            ...state.lessonSteps,
            { title, description: description || '', is_done: false },
        ];
        syncLessonSteps();

        await fetch(`/api/coding-sessions/${state.sessionId}/lesson-steps`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({ lesson_steps: state.lessonSteps }),
        });
    }

    async function addLessonFile() {
        const filename = window.prompt('File name', 'lesson-notes.md');
        if (!filename) {
            return;
        }

        const language = window.prompt('Language', 'markdown') || 'plaintext';
        const response = await fetch(`/api/coding-sessions/${state.sessionId}/files`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({ filename, language }),
        });

        if (!response.ok) {
            throw new Error(`File create failed with status ${response.status}`);
        }

        const data = await response.json();
        if (data.file) {
            state.files.push({ ...data.file });
            state.originalFiles.set(data.file.filename, { ...data.file });
            state.fileOrder.push(data.file.filename);
            renderFileNavigator();
            setActiveFile(data.file.filename);
        }
    }

    async function updatePermissions() {
        if (!state.isTeacher) {
            return;
        }

        const permissions = {
            edit: false,
            chat: false,
            pointer: false,
            code: false,
        };

        dom.permissionToggles.forEach((toggle) => {
            permissions[toggle.dataset.codingPermissionToggle] = Boolean(toggle.checked);
        });

        const response = await fetch(`/api/coding-sessions/${state.sessionId}/permissions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({ permissions }),
        });

        if (!response.ok) {
            throw new Error(`Permission update failed with status ${response.status}`);
        }

        state.permissions = { ...state.permissions, ...permissions };
        updateEditorEditable();
    }

    async function takeControl() {
        const response = await fetch(`/api/coding-sessions/${state.sessionId}/control/take`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Take control failed with status ${response.status}`);
        }

        state.controllerId = state.userId;
        updateEditorEditable();
        setTypingStatus('Teacher is editing');
    }

    async function releaseControl() {
        const response = await fetch(`/api/coding-sessions/${state.sessionId}/control/release`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Release control failed with status ${response.status}`);
        }

        state.controllerId = null;
        updateEditorEditable();
        setTypingStatus('Control returned');
    }

    async function requestTeacherHelp(route) {
        const response = await fetch(`/api/coding-sessions/${state.sessionId}/${route}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        setTypingStatus(route === 'raise-hand' ? 'Hand raised' : 'Help requested');
    }

    async function highlightSelection() {
        if (!editorView) {
            return;
        }

        const selection = editorView.state.selection.main;
        const line = editorView.state.doc.lineAt(selection.head);
        const lineStart = editorView.state.doc.lineAt(selection.from).number;
        const lineEnd = editorView.state.doc.lineAt(selection.to).number;
        const note = window.prompt('Add a correction or note', 'Teacher highlighted this block of code.');

        const response = await fetch(`/api/coding-sessions/${state.sessionId}/highlight`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
            body: JSON.stringify({
                file_id: currentFile()?.id,
                line_start: Math.min(lineStart, line.number),
                line_end: Math.max(lineEnd, line.number),
                note: note || '',
            }),
        });

        if (!response.ok) {
            throw new Error(`Highlight failed with status ${response.status}`);
        }

        rememberHighlight({
            file_id: currentFile()?.id,
            line_start: Math.min(lineStart, line.number),
            line_end: Math.max(lineEnd, line.number),
            note: note || '',
            user_id: state.userId,
            user_name: state.isTeacher ? 'Teacher / Tutor' : 'Learner',
        }, { announce: true });

        setTypingStatus('Highlight added');
    }

    async function submitWork() {
        const response = await fetch(`/api/coding-sessions/${state.sessionId}/submit`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Submit failed with status ${response.status}`);
        }

        setSaveLabel('Submitted', 'success');
        appendSystemMessage('Work submitted for teacher review.');
    }

    function appendSystemMessage(text) {
        const message = {
            id: `system-${Date.now()}`,
            user_id: null,
            user_name: 'System',
            message: text,
            message_type: 'system',
            created_at: new Date().toISOString(),
        };
        state.messages.push(message);
        appendChatMessage(message);
    }

    async function saveSessionSnapshot() {
        const response = await fetch(`/api/coding-sessions/${state.sessionId}/save-session`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': meta('csrf-token'),
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Save session failed with status ${response.status}`);
        }

        const data = await response.json();
        setSaveLabel(`Saved ${formatTime(data.saved_at)}`, 'success');
    }

    async function resetWorkspace() {
        if (!window.confirm('Reset the current lesson files back to the starter code?')) {
            return;
        }

        state.files = [...state.originalFiles.values()].map((file) => ({ ...file }));
        state.fileOrder = [...state.files].sort((left, right) => (left.sort_order ?? 0) - (right.sort_order ?? 0)).map((file) => file.filename);
        state.activeFileKey = state.fileOrder[0] || state.activeFileKey;
        renderFileNavigator();
        syncLessonSteps();
        syncParticipants();

        const active = currentFile();
        if (active) {
            replaceEditorDoc(active.content || '', active.language, { preserveSelection: false });
        }

        await persistAllFiles();
        runPreview();
        appendSystemMessage('Starter code restored.');
    }

    function updateFileTabHandlers() {
        [...dom.fileTabs, ...dom.fileButtons].forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.dataset.codingFileTab || button.dataset.codingFileKey;
                if (!key) {
                    return;
                }

                setActiveFile(key);
            });
        });
    }

    function updateOutputTabHandlers() {
        dom.outputTabs.forEach((button) => {
            button.addEventListener('click', () => {
                const tab = button.dataset.codingOutputTab;
                if (!tab) {
                    return;
                }

                renderOutputTab(tab);
            });
        });
    }

    function updateMobileTabHandlers() {
        dom.mobileTabs.forEach((button) => {
            button.addEventListener('click', () => {
                const tab = button.dataset.codingMobileTab;
                if (!tab) {
                    return;
                }
                renderMobileTab(tab);
            });
        });
    }

    function bindButtons() {
        dom.runBtn?.addEventListener('click', runCode);
        dom.saveBtn?.addEventListener('click', () => persistActiveFile().catch((error) => {
            console.error(error);
            setSaveLabel('Save failed', 'danger');
        }));
        dom.formatBtn?.addEventListener('click', () => formatActiveFile().catch((error) => appendErrorLine(error.message || 'Unable to format code.')));
        dom.resetBtn?.addEventListener('click', () => resetWorkspace().catch((error) => {
            console.error(error);
            appendErrorLine(error.message || 'Reset failed');
        }));
        dom.submitBtn?.addEventListener('click', () => submitWork().catch((error) => {
            console.error(error);
            appendErrorLine(error.message || 'Submit failed');
        }));
        dom.shareBtn?.addEventListener('click', () => copyText(state.joinLink));
        dom.inviteBtn?.addEventListener('click', () => copyText(state.joinLink));
        dom.themeToggle?.addEventListener('click', () => updateTheme(state.theme === 'dark' ? 'light' : 'dark'));
        dom.takeControlBtn?.addEventListener('click', () => takeControl().catch((error) => appendErrorLine(error.message || 'Unable to take control.')));
        dom.releaseControlBtn?.addEventListener('click', () => releaseControl().catch((error) => appendErrorLine(error.message || 'Unable to release control.')));
        dom.highlightBtn?.addEventListener('click', () => highlightSelection().catch((error) => appendErrorLine(error.message || 'Unable to highlight selection.')));
        dom.requestHelpBtn?.addEventListener('click', () => requestTeacherHelp('request-help').catch((error) => appendErrorLine(error.message || 'Unable to request help.')));
        dom.raiseHandBtn?.addEventListener('click', () => requestTeacherHelp('raise-hand').catch((error) => appendErrorLine(error.message || 'Unable to raise hand.')));
        dom.saveSessionBtn?.addEventListener('click', () => saveSessionSnapshot().catch((error) => appendErrorLine(error.message || 'Unable to save session.')));
        dom.addLessonStepBtn?.addEventListener('click', () => addLessonStep().catch((error) => appendErrorLine(error.message || 'Unable to add lesson step.')));
        dom.addFileBtn?.addEventListener('click', () => addLessonFile().catch((error) => appendErrorLine(error.message || 'Unable to add file.')));
        dom.chatSend?.addEventListener('click', () => sendChatMessage().catch((error) => appendErrorLine(error.message || 'Unable to send message.')));
        dom.chatNote?.addEventListener('click', () => sendChatMessage('instruction').catch((error) => appendErrorLine(error.message || 'Unable to add note.')));
        dom.chatInput?.addEventListener('keydown', (event) => {
            if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
                event.preventDefault();
                sendChatMessage().catch((error) => appendErrorLine(error.message || 'Unable to send message.'));
            }
        });

        dom.permissionToggles.forEach((toggle) => {
            toggle.addEventListener('change', () => updatePermissions().catch((error) => appendErrorLine(error.message || 'Unable to update permissions.')));
        });

        window.addEventListener('message', (event) => {
            if (event.data?.source !== 'cb-preview') {
                return;
            }

            if (event.data.type === 'console') {
                const payload = event.data.payload || {};
                appendConsoleLine(payload.level || 'log', payload.args || []);
                if (dom.testsPanel && !state.lastPreviewHadError) {
                    dom.testsPanel.innerHTML = '<p class="font-semibold">Running friendly checks...</p><p class="mt-2">Console output is visible. Keep going if the teacher asks you to improve the code.</p>';
                }
            }

            if (event.data.type === 'error') {
                const payload = event.data.payload || {};
                appendErrorLine(payload.message || 'A preview error occurred.');
            }
        });

        window.addEventListener('online', () => setConnectionState(state.echoConnected ? 'online' : 'connecting'));
        window.addEventListener('offline', () => setConnectionState('offline'));
        window.addEventListener('resize', () => renderMobileTab(state.mobileTab));

        window.addEventListener('beforeunload', () => {
            try {
                navigator.sendBeacon(`/api/coding-sessions/${state.sessionId}/leave`, new Blob([], { type: 'application/json' }));
            } catch {
                // Ignore unload errors.
            }
        });
    }

    function bindEcho() {
        if (!window.Echo || !state.sessionId) {
            setConnectionState('offline');
            return;
        }

        const channel = window.Echo.private(`coding.${state.sessionId}`);
        const listen = (eventName, callback) => channel.listen(`.${eventName}`, callback);

        listen('coding.code.updated', (payload) => {
            if (Number(payload.user_id) === Number(state.userId)) {
                return;
            }

            const file = payload.filename ? state.files.find((entry) => entry.filename === payload.filename) : null;
            if (!file) {
                return;
            }

            file.content = payload.content ?? '';
            file.language = payload.language || file.language;
            state.originalFiles.set(file.filename, { ...file });

            if (state.activeFileKey === file.filename && editorView && !state.dirty) {
                replaceEditorDoc(file.content, file.language);
            }

            setTypingStatus(`${payload.user_name || 'Teacher'} is editing ${file.filename}${payload.cursor_line ? ` line ${payload.cursor_line}` : ''}`);
            setSaveLabel(`${payload.user_name || 'Teacher'} updated code`, 'info');
            runPreviewSoon();
        });

        listen('coding.code.saved', (payload) => {
            if (Number(payload.user_id) !== Number(state.userId)) {
                setSaveLabel(`${payload.user_name || 'Teacher'} saved`, 'success');
            }
        });

        listen('coding.chat.message.sent', (payload) => {
            recordIncomingMessage(payload);
        });

        listen('coding.participant.joined', (payload) => {
            const existing = state.participants.find((participant) => Number(participant.user_id) === Number(payload.user_id));
            if (existing) {
                existing.is_active = true;
                existing.typing_status = payload.typing_status || existing.typing_status;
            } else {
                state.participants.push({
                    id: payload.event_id || Date.now(),
                    user_id: payload.user_id,
                    name: payload.user_name || 'Participant',
                    role: payload.role_in_session || 'student',
                    is_active: true,
                    typing_status: payload.typing_status || '',
                    permissions: payload.permissions || {},
                });
            }
            syncParticipants();
            appendSystemMessage(`${payload.user_name || 'A participant'} joined the coding session.`);
        });

        listen('coding.participant.left', (payload) => {
            const participant = state.participants.find((item) => Number(item.user_id) === Number(payload.user_id));
            if (participant) {
                participant.is_active = false;
            }
            syncParticipants();
            appendSystemMessage(`${payload.user_name || 'A participant'} left the coding session.`);
        });

        listen('coding.cursor.moved', (payload) => {
            if (Number(payload.user_id) === Number(state.userId)) {
                return;
            }

            const participant = state.participants.find((item) => Number(item.user_id) === Number(payload.user_id));
            if (participant) {
                participant.cursor_line = payload.cursor_line || participant.cursor_line;
                participant.cursor_column = payload.cursor_column || participant.cursor_column;
                participant.active_file_key = payload.active_file_key || participant.active_file_key;
                participant.typing_status = payload.typing_status || participant.typing_status;
            }
            syncParticipants();
            setTypingStatus(`${payload.user_name || 'Teacher'} is editing ${payload.active_file_key || state.activeFileKey}${payload.cursor_line ? ` line ${payload.cursor_line}` : ''}`);
        });

        listen('coding.control.changed', (payload) => {
            state.controllerId = payload.editor_controller_id ?? null;
            updateEditorEditable();
            const controller = payload.editor_controller_name || 'Teacher';
            setTypingStatus(payload.mode === 'teacher' ? `${controller} has control` : 'Control returned to the learner');
        });

        listen('coding.student.permission.changed', (payload) => {
            if (payload.permissions) {
                state.permissions = { ...state.permissions, ...payload.permissions };
                updateEditorEditable();
            }
        });

        listen('coding.session.status.changed', (payload) => {
            if (payload.status) {
                state.session.status = payload.status;
                setConnectionState(payload.status === 'live' ? 'online' : (payload.status === 'ended' ? 'offline' : 'connecting'));
                setTypingStatus(payload.status === 'ended' ? 'Session ended' : 'Session live');
            }
        });

        listen('coding.lesson.steps.updated', (payload) => {
            state.lessonSteps = [...(payload.lesson_steps || [])];
            syncLessonSteps();
            appendSystemMessage('Lesson steps were updated by the teacher.');
        });

        listen('coding.line.highlighted', (payload) => {
            if (Number(payload.user_id) === Number(state.userId)) {
                return;
            }

            rememberHighlight(payload, { announce: true });
        });

        listen('coding.file.changed', (payload) => {
            if (payload.active_file_key) {
                state.activeFileKey = payload.active_file_key;
                renderFileNavigator();
                renderHighlights();
            }
        });

        listen('coding.work.submitted', (payload) => {
            appendSystemMessage(`${payload.user_name || 'Student'} submitted the work for review.`);
        });

        setConnectionState('online');
        state.echoConnected = true;
    }

    function handleEditorUpdate(update) {
        if (state.applyingRemote) {
            return;
        }

        if (update.docChanged) {
            updateFileContentFromEditor();
        }

        if (update.selectionSet) {
            markSelection();
        }
    }

    function initEditor() {
        const file = currentFile();
        const extensions = [
            lineNumbers(),
            highlightActiveLineGutter(),
            highlightActiveLine(),
            drawSelection(),
            dropCursor(),
            rectangularSelection(),
            history(),
            indentOnInput(),
            bracketMatching(),
            autocompletion(),
            closeBrackets(),
            syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
            keymap.of([
                ...defaultKeymap,
                ...historyKeymap,
                indentWithTab,
                ...completionKeymap,
            ]),
            EditorView.lineWrapping,
            themeCompartment.of(state.theme === 'dark' ? createDarkTheme() : createLightTheme()),
            languageCompartment.of(languageForFile(file)),
            editableCompartment.of(EditorView.editable.of(canEdit())),
            highlightField,
            EditorView.updateListener.of(handleEditorUpdate),
        ];

        editorView = new EditorView({
            state: EditorState.create({
                doc: file?.content || '',
                extensions,
            }),
            parent: dom.editor,
        });
    }

    function setInitialUi() {
        renderFileNavigator();
        syncLessonSteps();
        syncParticipants();
        syncChatMessages();
        hydrateHighlightsFromEvents(state.events);
        renderOutputTab('preview');
        updateTheme(state.theme);
        renderMobileTab('editor');
        setConnectionState(navigator.onLine ? 'connecting' : 'offline');
        if (dom.syncStatus) {
            dom.syncStatus.textContent = 'Connecting...';
        }
        if (dom.sessionStatusText) {
            dom.sessionStatusText.textContent = 'Real-time sync will connect teacher and student actions instantly using WebSocket broadcasting.';
        }
        if (dom.previewFrame) {
            dom.previewFrame.srcdoc = '<!doctype html><html><body style="font-family: Inter, system-ui, sans-serif; display:flex; min-height:100%; align-items:center; justify-content:center; background:#f8fafc; color:#475569;"><div style="max-width:640px; padding:24px; text-align:center;"><h1 style="margin:0 0 12px; font-size:28px; color:#0f172a;">Run the code to preview the lesson</h1><p style="margin:0;">This protected preview keeps the student inside the ClassBridge AI workspace.</p></div></body></html>';
        }
    }

    function initCopyButtons() {
        document.querySelectorAll('[data-copy-text]').forEach((button) => {
            button.addEventListener('click', () => {
                copyText(button.dataset.copyText || button.getAttribute('data-copy-text') || '')
                    .then(() => {
                        button.classList.add('ring-2', 'ring-emerald-400/40');
                        setTimeout(() => button.classList.remove('ring-2', 'ring-emerald-400/40'), 1200);
                    })
                    .catch(() => {});
            });
        });
    }

    bindButtons();
    updateFileTabHandlers();
    updateOutputTabHandlers();
    updateMobileTabHandlers();
    initCopyButtons();
    initEditor();
    setInitialUi();
    loadState().then(() => {
        updateEditorEditable();
        runPreview();
    }).catch(() => {
        runPreview();
    });
    bindEcho();
    renderOutputTab('preview');
}

initCodingStudio();
