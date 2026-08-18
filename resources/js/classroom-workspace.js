import { EditorState, Compartment } from '@codemirror/state';
import {
    EditorView,
    lineNumbers,
    highlightActiveLine,
    highlightActiveLineGutter,
    drawSelection,
    dropCursor,
    rectangularSelection,
    keymap,
} from '@codemirror/view';
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands';
import { autocompletion, closeBrackets, completionKeymap } from '@codemirror/autocomplete';
import { syntaxHighlighting, defaultHighlightStyle, indentOnInput, bracketMatching } from '@codemirror/language';
import { html } from '@codemirror/lang-html';
import { css } from '@codemirror/lang-css';
import { javascript } from '@codemirror/lang-javascript';
import { php } from '@codemirror/lang-php';
import { createWhiteboardFoundation } from './whiteboard-foundation';

const MODE_LABELS = {
    whiteboard: 'Whiteboard Mode',
    coding: 'Coding Mode',
    text: 'Text Pad',
    english: 'Text Pad',
    mathematics: 'Mathematics Mode',
    math: 'Mathematics Mode',
    presentation: 'Presentation Mode',
};

const PRIMARY_MODES = ['whiteboard', 'coding', 'text', 'mathematics', 'presentation'];
const WORKSPACE_PANEL_FOR_MODE = {
    whiteboard: 'whiteboard',
    mathematics: 'whiteboard',
    coding: 'coding',
    text: 'text',
    english: 'text',
    presentation: 'presentation',
};
const PERMISSION_LABELS = {
    draw: 'Draw',
    type: 'Type',
    chat: 'Chat',
    pointer: 'Pointer',
    code: 'Code',
    download: 'Download',
};
const TEXTPAD_ALLOWED_TAGS = new Set(['P', 'DIV', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'UL', 'OL', 'LI', 'H1', 'H2', 'H3', 'BLOCKQUOTE', 'SPAN']);
const TEXTPAD_ALLOWED_STYLE_PROPERTIES = new Set(['text-align']);

const CLASSROOM_CODE_THEME = EditorView.theme({
    '&': {
        height: '100%',
        backgroundColor: '#0f172a',
        color: '#e2e8f0',
    },
    '.cm-scroller': {
        fontFamily: '"Instrument Sans", ui-sans-serif, system-ui, sans-serif',
        lineHeight: '1.7',
        color: '#e2e8f0',
    },
    '.cm-content, .cm-gutter': {
        minHeight: '100%',
    },
    '.cm-content': {
        padding: '16px 0 24px',
    },
    '.cm-gutters': {
        borderRight: '1px solid rgba(71, 85, 105, 0.9)',
        backgroundColor: '#111827',
        color: '#94a3b8',
    },
    '.cm-activeLine': {
        backgroundColor: 'rgba(148, 163, 184, 0.12)',
    },
    '.cm-activeLineGutter': {
        backgroundColor: 'rgba(15, 23, 42, 0.4)',
    },
    '.cm-selectionBackground, .cm-content ::selection': {
        backgroundColor: 'rgba(56, 189, 248, 0.25)',
    },
    '&.cm-focused .cm-cursor, .cm-cursor': {
        borderLeftColor: '#f8fafc',
    },
    '.cm-tooltip': {
        border: '1px solid rgba(71, 85, 105, 0.9)',
        backgroundColor: '#111827',
        color: '#e2e8f0',
        borderRadius: '16px',
    },
}, { dark: true });

function meta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.content ?? '';
}

function displayName(person) {
    if (!person) {
        return 'User';
    }

    if (typeof person.displayName === 'function') {
        return person.displayName();
    }

    return [
        person.first_name ?? person.firstName ?? '',
        person.last_name ?? person.lastName ?? '',
    ].filter(Boolean).join(' ').trim()
        || person.name
        || person.user_name
        || 'User';
}

function formatClock(value) {
    const date = value ? new Date(value) : new Date();
    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    });
}

function sortElements(elements) {
    return [...(elements ?? [])].sort((left, right) => {
        const leftDate = new Date(left.created_at ?? 0).getTime();
        const rightDate = new Date(right.created_at ?? 0).getTime();
        if (leftDate !== rightDate) {
            return leftDate - rightDate;
        }

        return (left.id ?? 0) - (right.id ?? 0);
    });
}

function getPoint(canvas, event) {
    const rect = canvas.getBoundingClientRect();

    return {
        x: Number((event.clientX - rect.left).toFixed(2)),
        y: Number((event.clientY - rect.top).toFixed(2)),
    };
}

function clampText(text) {
    return String(text ?? '').trim();
}

function readMetaJson(name, fallback = {}) {
    try {
        const raw = meta(name);
        if (!raw) {
            return fallback;
        }

        return JSON.parse(raw);
    } catch {
        return fallback;
    }
}

function deepClone(value) {
    try {
        return JSON.parse(JSON.stringify(value ?? null));
    } catch {
        return value ?? null;
    }
}

function normalizeCodeFileKey(key) {
    const value = String(key ?? '').trim().toLowerCase();
    const safe = value
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9._-]/g, '')
        .replace(/-+/g, '-')
        .replace(/^-+|-+$/g, '');

    return safe || 'html';
}

function defaultCodeFiles() {
    return {
        html: {
            filename: 'index.html',
            language: 'html',
            content: `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JavaScript Live</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <main class="container">
    <h1>Welcome to ClassBridge AI</h1>
    <p id="message">Edit the code and click Run Preview.</p>
    <button id="btn">Click me</button>
  </main>
  <script src="script.js"></script>
</body>
</html>`,
            label: 'HTML',
        },
        css: {
            filename: 'styles.css',
            language: 'css',
            content: `body {
  font-family: Arial, sans-serif;
  background: #ffffff;
  color: #0f172a;
}

.container {
  padding: 32px;
}

button {
  border: 0;
  border-radius: 8px;
  background: #16a34a;
  color: white;
  padding: 10px 16px;
  font-weight: 700;
}`,
            label: 'CSS',
        },
        js: {
            filename: 'script.js',
            language: 'javascript',
            content: `const button = document.querySelector('#btn');
const message = document.querySelector('#message');

button.addEventListener('click', () => {
  message.textContent = 'Great work. Your preview is interactive.';
});`,
            label: 'JavaScript',
        },
    };
}

function codeLanguageFromFilename(filename = '', fallback = 'javascript') {
    const lower = String(filename ?? '').trim().toLowerCase();

    if (lower.endsWith('.html') || lower.endsWith('.htm')) {
        return 'html';
    }

    if (lower.endsWith('.css')) {
        return 'css';
    }

    if (lower.endsWith('.js') || lower.endsWith('.mjs') || lower.endsWith('.cjs')) {
        return 'javascript';
    }

    if (lower.endsWith('.php')) {
        return 'php';
    }

    return fallback;
}

function codeLabelForLanguage(language = '') {
    const value = String(language ?? '').trim().toLowerCase();

    if (value === 'html') {
        return 'HTML';
    }

    if (value === 'css') {
        return 'CSS';
    }

    if (value === 'javascript' || value === 'js') {
        return 'JavaScript';
    }

    if (value === 'php') {
        return 'PHP';
    }

    return value ? value.toUpperCase() : 'TEXT';
}

function codeFileSortWeight(key, file) {
    const priority = { html: 0, css: 1, js: 2 };
    const normalizedKey = String(key ?? '').trim().toLowerCase();
    if (normalizedKey in priority) {
        return priority[normalizedKey];
    }

    if (Number.isFinite(Number(file?.sort_order))) {
        return 100 + Number(file.sort_order);
    }

    return 1000;
}

function orderedCodeFileEntries(files = {}) {
    return Object.entries(files || {})
        .filter(([key, value]) => key && value && typeof value === 'object')
        .sort(([leftKey, leftFile], [rightKey, rightFile]) => {
            const leftWeight = codeFileSortWeight(leftKey, leftFile);
            const rightWeight = codeFileSortWeight(rightKey, rightFile);
            if (leftWeight !== rightWeight) {
                return leftWeight - rightWeight;
            }

            const leftName = String(leftFile?.filename ?? leftKey);
            const rightName = String(rightFile?.filename ?? rightKey);
            return leftName.localeCompare(rightName);
        });
}

function uniqueCodeFileKey(base, existingKeys = []) {
    const safeBase = normalizeCodeFileKey(base);
    if (!existingKeys.includes(safeBase)) {
        return safeBase;
    }

    let index = 2;
    let candidate = `${safeBase}-${index}`;
    while (existingKeys.includes(candidate)) {
        index += 1;
        candidate = `${safeBase}-${index}`;
    }

    return candidate;
}

function codeFileTemplateFromFilename(filename = '', fallbackLanguage = 'javascript') {
    const normalizedName = String(filename ?? '').trim();
    const language = codeLanguageFromFilename(normalizedName, fallbackLanguage);

    return {
        filename: normalizedName || `untitled.${language === 'css' ? 'css' : language === 'html' ? 'html' : language === 'php' ? 'php' : 'js'}`,
        language,
        content: '',
        label: codeLabelForLanguage(language),
    };
}

function defaultWhiteboardState() {
    return {
        active_page: 'page-1',
        zoom: 100,
        viewport: {
            x: 0,
            y: 0,
        },
        pages: [
            {
                key: 'page-1',
                name: 'Page 1',
                sort_order: 0,
            },
        ],
    };
}

function classroomLanguageForFile(file) {
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

function normalizeCodeFiles(source = {}, fallback = {}) {
    const defaults = defaultCodeFiles();
    const files = source && typeof source === 'object' ? source : {};
    const fallbackFiles = fallback && typeof fallback === 'object' ? fallback : {};
    const normalized = {};

    Object.entries(defaults).forEach(([key, template]) => {
        const candidate = files[key] || fallbackFiles[key] || {};

        normalized[key] = {
            filename: candidate.filename || template.filename,
            language: candidate.language || template.language,
            content: candidate.content || template.content,
            label: candidate.label || template.label,
            sort_order: Number.isFinite(Number(candidate.sort_order)) ? Number(candidate.sort_order) : template.sort_order || 0,
        };
    });

    Object.entries({ ...fallbackFiles, ...files }).forEach(([key, candidate]) => {
        if (!key || normalized[key]) {
            return;
        }

        const template = codeFileTemplateFromFilename(candidate?.filename || key, candidate?.language || 'javascript');
        normalized[key] = {
            filename: candidate?.filename || template.filename,
            language: candidate?.language || template.language,
            content: candidate?.content || '',
            label: candidate?.label || template.label,
            sort_order: Number.isFinite(Number(candidate?.sort_order)) ? Number(candidate.sort_order) : 0,
        };
    });

    return normalized;
}

function escapeScriptContent(content) {
    return String(content ?? '').replace(/<\/script>/gi, '<\\/script>');
}

function splitHtmlPreviewContent(htmlContent) {
    const raw = String(htmlContent ?? '');

    if (!raw.trim()) {
        return {
            head: '',
            body: '',
        };
    }

    if (!/<\/?\s*(html|head|body)\b/i.test(raw) && !/<!doctype/i.test(raw)) {
        return {
            head: '',
            body: raw,
        };
    }

    try {
        const parsed = new DOMParser().parseFromString(raw, 'text/html');
        return {
            head: parsed.head?.innerHTML || '',
            body: parsed.body?.innerHTML || raw,
        };
    } catch {
        return {
            head: '',
            body: raw,
        };
    }
}

function buildPreviewDocument(codeFiles) {
    const entries = orderedCodeFileEntries(codeFiles);
    const htmlFiles = entries.filter(([, file]) => codeLanguageFromFilename(file?.filename, file?.language) === 'html');
    const cssFiles = entries.filter(([, file]) => codeLanguageFromFilename(file?.filename, file?.language) === 'css');
    const jsFiles = entries.filter(([, file]) => {
        const language = codeLanguageFromFilename(file?.filename, file?.language);
        return language === 'javascript';
    });

    const htmlSource = String(htmlFiles[0]?.[1]?.content ?? codeFiles.html?.content ?? '');
    const htmlSegments = splitHtmlPreviewContent(htmlSource);
    const css = cssFiles.map(([, file]) => String(file?.content ?? '')).join('\n\n');
    const js = escapeScriptContent(jsFiles.map(([, file]) => String(file?.content ?? '')).join('\n\n'));

    return `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="https://preview.classbridge.live/">
  ${htmlSegments.head}
  <style>
    :root { color-scheme: light; }
    body { margin: 0; font-family: "Instrument Sans", system-ui, sans-serif; background: #ffffff; color: #0f172a; }
    * { box-sizing: border-box; }
    .cb-preview-root { min-height: 100vh; padding: 1rem; }
    ${css}
  </style>
</head>
<body>
  <div class="cb-preview-root">${htmlSegments.body}</div>
  <script>
    (function () {
      const send = (type, payload) => {
        try {
          parent.postMessage({ source: 'classbridge-preview', type, payload }, '*');
        } catch (error) {
          console.warn(error);
        }
      };

      const format = (value) => {
        if (typeof value === 'string') return value;
        if (value instanceof Error) return value.message || 'Error';
        try { return JSON.stringify(value); } catch { return String(value); }
      };

      const originalLog = console.log.bind(console);
      const originalWarn = console.warn.bind(console);
      const originalError = console.error.bind(console);

      console.log = (...args) => {
        send('log', args.map(format).join(' '));
        originalLog(...args);
      };

      console.warn = (...args) => {
        send('warn', args.map(format).join(' '));
        originalWarn(...args);
      };

      console.error = (...args) => {
        send('error', args.map(format).join(' '));
        originalError(...args);
      };

      window.addEventListener('error', (event) => {
        send('error', event.message || 'Something went wrong in the preview.');
      });

      window.addEventListener('unhandledrejection', (event) => {
        send('error', format(event.reason || 'Preview promise rejected.'));
      });

      try {
        ${js}
      } catch (error) {
        send('error', format(error));
      }

      send('ready', 'Preview loaded.');
    })();
  </script>
</body>
</html>`;
}

function createSessionConfig() {
    const sessionId = meta('classroom-session-id');
    if (!sessionId) {
        return null;
    }

    return {
        sessionId,
        userId: Number(meta('classroom-user-id') || 0),
        isTeacher: meta('classroom-is-teacher') === '1',
        permissions: readMetaJson('classroom-permissions', {}),
        roomPermissions: readMetaJson('classroom-room-permissions', {}),
        currentMode: meta('classroom-mode') || 'whiteboard',
        roomCode: meta('classroom-room-code'),
        joinLink: meta('classroom-join-link'),
        codeDraft: readMetaJson('classroom-code-draft', ''),
        codeLanguage: meta('classroom-code-language') || 'plaintext',
        codeWorkspace: readMetaJson('classroom-code-workspace', {}),
        codeFiles: readMetaJson('classroom-code-files', {}),
        codeActiveFileKey: meta('classroom-code-active-file') || 'html',
        textPadContent: readMetaJson('classroom-textpad-content', ''),
        textPadComments: readMetaJson('classroom-textpad-comments', []),
        sessionNotes: readMetaJson('classroom-session-notes', ''),
        sessionResources: readMetaJson('classroom-session-resources', []),
        whiteboardState: readMetaJson('classroom-whiteboard-state', defaultWhiteboardState()),
    };
}

function classroomActionHeaders() {
    const csrf = meta('csrf-token');

    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
    };
}

function canEditWhiteboard(state) {
    const boardLocked = Boolean(state.whiteboardState?.settings?.board_locked ?? false);

    return state.isTeacher || (!boardLocked && Boolean(
        state.permissions.whiteboard_draw
            ?? state.permissions.draw
            ?? state.roomPermissions.whiteboard_draw
            ?? state.roomPermissions.draw
    ));
}

function canUsePointer(state) {
    return state.isTeacher || Boolean(
        state.permissions.whiteboard_pointer
            ?? state.permissions.pointer
            ?? state.roomPermissions.whiteboard_pointer
            ?? state.roomPermissions.pointer
    );
}

function canUseTextPad(state) {
    return state.isTeacher || Boolean(
        state.permissions.whiteboard_text
            ?? state.permissions.type
            ?? state.roomPermissions.whiteboard_text
            ?? state.roomPermissions.type
    );
}

function canUseCodeEditor(state) {
    return state.isTeacher || Boolean(state.permissions.code ?? state.roomPermissions.code ?? state.permissions.type ?? state.roomPermissions.type);
}

function canManagePermissions(state) {
    return state.isTeacher;
}

function normalizeTextPadComments(comments) {
    return (Array.isArray(comments) ? comments : [])
        .map((comment, index) => {
            if (!comment || typeof comment !== 'object') {
                return null;
            }

            const message = clampText(comment.message);
            if (!message) {
                return null;
            }

            return {
                id: clampText(comment.id) || `comment-${index + 1}`,
                message,
                author_name: clampText(comment.author_name) || 'Teacher',
                created_at: clampText(comment.created_at) || new Date().toISOString(),
                user_id: Number(comment.user_id || 0) || null,
            };
        })
        .filter(Boolean);
}

function sanitizeTextPadHtml(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(`<div>${String(html ?? '')}</div>`, 'text/html');
    const root = doc.body.firstElementChild;

    if (!root) {
        return '';
    }

    const sanitizeNode = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) {
                return;
            }

            if (child.nodeType !== Node.ELEMENT_NODE) {
                child.remove();
                return;
            }

            const tagName = child.tagName.toUpperCase();
            if (!TEXTPAD_ALLOWED_TAGS.has(tagName)) {
                const fragment = doc.createDocumentFragment();
                while (child.firstChild) {
                    fragment.appendChild(child.firstChild);
                }
                child.replaceWith(fragment);
                return;
            }

            [...child.attributes].forEach((attribute) => {
                if (attribute.name !== 'style') {
                    child.removeAttribute(attribute.name);
                }
            });

            if (child.hasAttribute('style')) {
                const safeStyle = child
                    .getAttribute('style')
                    .split(';')
                    .map((rule) => rule.trim())
                    .filter(Boolean)
                    .map((rule) => {
                        const [property, value] = rule.split(':').map((part) => part.trim());
                        if (!property || !value) {
                            return null;
                        }

                        return TEXTPAD_ALLOWED_STYLE_PROPERTIES.has(property.toLowerCase())
                            ? `${property}: ${value}`
                            : null;
                    })
                    .filter(Boolean)
                    .join('; ');

                if (safeStyle) {
                    child.setAttribute('style', safeStyle);
                } else {
                    child.removeAttribute('style');
                }
            }

            sanitizeNode(child);
        });
    };

    sanitizeNode(root);
    return root.innerHTML.trim();
}

function textPadPlainTextFromHtml(html) {
    const wrapper = document.createElement('div');
    wrapper.innerHTML = sanitizeTextPadHtml(html);
    return (wrapper.textContent || '')
        .replace(/\u00a0/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function wordCountFromText(text) {
    if (!clampText(text)) {
        return 0;
    }

    return clampText(text).split(/\s+/).length;
}

function isTextPadBlankHtml(html) {
    return clampText(textPadPlainTextFromHtml(html)) === '';
}

function copyTextToClipboard(text) {
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

function toggleTab(dom, tabName) {
    dom.tabButtons.forEach((button) => {
        const active = button.dataset.classroomTab === tabName;
        button.classList.toggle('cb-right-rail-tab-active', active);
        button.classList.toggle('cb-right-rail-tab-inactive', !active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    dom.panels.forEach((panel) => {
        const panelKey = panel.dataset.classroomPanel || '';
        panel.classList.toggle('hidden', panelKey !== tabName);
    });
}

function getTextPadHtml(dom) {
    return sanitizeTextPadHtml(dom.textpadEditor?.innerHTML || '');
}

function setTextPadHtml(dom, html = '') {
    if (!dom.textpadEditor) {
        return;
    }

    const safeHtml = sanitizeTextPadHtml(html);
    dom.textpadEditor.innerHTML = safeHtml;
    dom.textpadEditor.dataset.empty = isTextPadBlankHtml(safeHtml) ? 'true' : 'false';
}

function updateTextPadWordCount(dom, html = '') {
    if (!dom.textpadWordCount) {
        return;
    }

    const count = wordCountFromText(textPadPlainTextFromHtml(html));
    dom.textpadWordCount.textContent = `${count} ${count === 1 ? 'word' : 'words'}`;
}

function updateTextPadSelectionStatus(dom) {
    if (!dom.textpadSelectionStatus || !dom.textpadEditor) {
        return;
    }

    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0 || !dom.textpadEditor.contains(selection.anchorNode)) {
        dom.textpadSelectionStatus.textContent = 'Cursor ready';
        return;
    }

    const range = selection.getRangeAt(0);
    dom.textpadSelectionStatus.textContent = range.collapsed
        ? 'Typing cursor active'
        : `${selection.toString().trim().split(/\s+/).filter(Boolean).length || 1} words selected`;
}

function renderTextPadComments(dom, state) {
    if (!dom.textpadCommentsList) {
        return;
    }

    dom.textpadCommentsList.innerHTML = '';

    if (!state.textPadComments.length) {
        const empty = document.createElement('div');
        empty.className = 'rounded-[1.25rem] border border-dashed border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-500';
        empty.textContent = state.isTeacher
            ? 'Add a correction or writing prompt for the learner.'
            : 'Teacher feedback will appear here while the lesson is live.';
        dom.textpadCommentsList.appendChild(empty);
    } else {
        state.textPadComments.forEach((comment) => {
            const item = document.createElement('article');
            item.className = 'rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-3';

            const metaLine = document.createElement('div');
            metaLine.className = 'flex items-center justify-between gap-3 text-xs text-slate-500';
            const author = document.createElement('span');
            author.className = 'font-semibold text-slate-700';
            author.textContent = comment.author_name || 'Teacher';
            const clock = document.createElement('span');
            clock.textContent = formatClock(comment.created_at);
            metaLine.append(author, clock);

            const body = document.createElement('p');
            body.className = 'mt-2 text-sm leading-6 text-slate-700';
            body.textContent = comment.message || '';

            item.append(metaLine, body);
            dom.textpadCommentsList.appendChild(item);
        });
    }

    if (dom.textpadCommentCount) {
        dom.textpadCommentCount.textContent = String(state.textPadComments.length);
    }
}

function updateTextPadTypingStatus(dom, message) {
    if (dom.textpadTypingStatus) {
        dom.textpadTypingStatus.textContent = message;
    }
}

function updateTextPadCollabState(dom, state) {
    if (dom.textpadCollabStatus) {
        dom.textpadCollabStatus.textContent = canUseTextPad(state)
            ? (state.isTeacher ? 'Shared writing enabled' : 'You can type in this lesson')
            : 'Teacher-only writing right now';
    }

    if (dom.textpadLanguageLabel) {
        dom.textpadLanguageLabel.textContent = state.currentMode === 'text' ? 'English / Writing' : 'Lesson writing';
    }
}

function focusTextPadEditor(dom) {
    if (!dom.textpadEditor) {
        return;
    }

    dom.textpadEditor.focus();
}

function execTextPadCommand(dom, state, command, value = null) {
    if (!dom.textpadEditor || !canUseTextPad(state)) {
        return false;
    }

    focusTextPadEditor(dom);
    try {
        return document.execCommand(command, false, value);
    } catch {
        return false;
    }
}

function appendTextPadComment(state, dom, message) {
    const content = clampText(message);
    if (!content) {
        return false;
    }

    state.textPadComments = normalizeTextPadComments([
        ...(state.textPadComments || []),
        {
            id: `comment-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
            message: content,
            author_name: state.isTeacher ? 'Teacher' : 'Learner',
            created_at: new Date().toISOString(),
            user_id: state.userId || null,
        },
    ]);

    renderTextPadComments(dom, state);
    return true;
}

function appendSystemLine(dom, message) {
    if (!dom.activityFeed) {
        return;
    }

    const item = document.createElement('div');
    item.dataset.activityItem = 'true';
    item.className = 'rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600';
    item.textContent = `${formatClock()} ${message}`;
    dom.activityFeed.prepend(item);

    while (dom.activityFeed.children.length > 8) {
        dom.activityFeed.removeChild(dom.activityFeed.lastElementChild);
    }
}

function renderMessage(dom, state, message, { prepend = false } = {}) {
    if (!message || !dom.chatMessages) {
        return;
    }

    if (state.messageIds.has(String(message.id))) {
        return;
    }

    state.messageIds.add(String(message.id));
    state.lastMessageId = Math.max(state.lastMessageId, Number(message.id) || 0);

    const mine = Number(message.user_id) === Number(state.userId);
    const senderName = mine ? 'You' : (message.user_name || displayName(message.user));
    const initials = senderName
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0] || '')
        .join('')
        .slice(0, 2)
        .toUpperCase() || 'U';

    const wrapper = document.createElement('div');
    wrapper.className = `flex items-start gap-3 ${mine ? 'flex-row-reverse' : ''}`;

    const avatar = document.createElement('div');
    avatar.className = `grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-black ${
        mine ? 'bg-emerald-600 text-white' : 'bg-slate-950 text-white'
    }`;
    avatar.textContent = initials;

    const stack = document.createElement('div');
    stack.className = `max-w-[82%] ${mine ? 'text-right' : ''}`;

    const metaRow = document.createElement('div');
    metaRow.className = `mb-1 flex items-center gap-2 text-[11px] font-semibold text-slate-500 ${mine ? 'justify-end' : ''}`;

    const sender = document.createElement('span');
    sender.className = 'text-slate-700';
    sender.textContent = senderName;

    const role = document.createElement('span');
    role.textContent = message.role_in_session || (mine ? 'You' : 'Participant');

    const clock = document.createElement('span');
    clock.textContent = formatClock(message.created_at);

    metaRow.append(sender, role, clock);

    const bubble = document.createElement('div');
    bubble.className = `rounded-2xl px-4 py-3 text-sm shadow-sm ${
        mine ? 'bg-emerald-50 text-emerald-950' : 'bg-slate-100 text-slate-800'
    }`;

    const body = document.createElement('p');
    body.className = 'whitespace-pre-wrap leading-6';
    body.textContent = message.message || '';

    bubble.appendChild(body);
    stack.append(metaRow, bubble);
    wrapper.append(avatar, stack);

    if (prepend) {
        dom.chatMessages.appendChild(wrapper);
    } else {
        dom.chatMessages.appendChild(wrapper);
    }

    dom.chatMessages.scrollTop = dom.chatMessages.scrollHeight;
}

function renderParticipants(dom, participants, state = null) {
    if (!dom.participantsList) {
        return;
    }

    dom.participantsList.innerHTML = '';

    if (!participants.length) {
        const empty = document.createElement('p');
        empty.className = 'rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500';
        empty.textContent = 'No participants are in the room yet.';
        dom.participantsList.appendChild(empty);
        if (dom.participantCount) {
            dom.participantCount.textContent = '0';
        }
        return;
    }

    if (dom.participantCount) {
        dom.participantCount.textContent = String(participants.length);
    }

    participants.forEach((participant) => {
        const label = displayName(participant.user);
        const initials = (label
            .split(' ')
            .filter(Boolean)
            .map((part) => part[0] || '')
            .join('')
            .slice(0, 2) || 'U').toUpperCase();

        const isTeacher = participant.role_in_session === 'teacher';
        const row = document.createElement('article');
        row.className = 'flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-3';

        const left = document.createElement('div');
        left.className = 'flex min-w-0 items-center gap-3';

        const avatar = document.createElement('div');
        avatar.className = `relative grid h-10 w-10 shrink-0 place-items-center rounded-full text-xs font-black ${
            isTeacher
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'bg-slate-950 text-white'
        }`;
        avatar.textContent = initials;

        const dot = document.createElement('span');
        dot.className = 'absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500';
        avatar.appendChild(dot);

        const copy = document.createElement('div');
        copy.className = 'min-w-0';
        const name = document.createElement('p');
        name.className = 'truncate text-sm font-bold text-slate-900';
        name.textContent = label;
        const status = document.createElement('p');
        status.className = 'mt-0.5 text-xs font-medium text-slate-500';
        status.textContent = `${isTeacher ? 'Teacher / Tutor' : 'Learner'} - Online - Mic ready`;
        copy.append(name, status);

        left.append(avatar, copy);

        const actions = document.createElement('div');
        actions.className = 'flex shrink-0 items-center gap-2';

        const badge = document.createElement('span');
        badge.className = `rounded-full px-2.5 py-1 text-[11px] font-semibold ${
            isTeacher ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'
        }`;
        badge.textContent = isTeacher ? 'Teacher' : 'Learner';
        actions.appendChild(badge);

        if (state?.isTeacher && !isTeacher) {
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold text-slate-500 transition hover:border-red-200 hover:text-red-600';
            remove.dataset.removeParticipant = participant.id || '';
            remove.textContent = 'Remove';
            actions.appendChild(remove);
        }

        row.append(left, actions);
        dom.participantsList.appendChild(row);
    });
}

    function updateModeUi(dom, state, mode) {
        state.currentMode = mode || 'whiteboard';
        const workspaceMode = WORKSPACE_PANEL_FOR_MODE[state.currentMode] || state.currentMode;

    if (dom.modeBadge) {
        dom.modeBadge.textContent = MODE_LABELS[state.currentMode] || state.currentMode;
    }

    if (dom.modeButtons.length) {
        dom.modeButtons.forEach((button) => {
            const active = button.dataset.mode === state.currentMode;
            button.classList.toggle('cb-classroom-mode-tab-active', active);
            button.classList.toggle('cb-classroom-mode-tab-inactive', !active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    if (dom.workspacePanels?.length) {
        dom.workspacePanels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.workspacePanel !== workspaceMode);
        });
    }

        if (dom.modeVariantGroups?.length) {
            dom.modeVariantGroups.forEach((group) => {
                const variant = group.dataset.modeVariant;
                const visible = variant === state.currentMode || (variant === 'whiteboard' && state.currentMode === 'whiteboard') || (variant === 'mathematics' && state.currentMode === 'mathematics');
                group.classList.toggle('hidden', !visible);
            });
        }

        if (workspaceMode === 'coding') {
            refreshCodeEditorLayout();
        }
    }

function drawTextLines(ctx, text, x, y, lineHeight) {
    String(text ?? '').split('\n').forEach((line, index) => {
        ctx.fillText(line, x, y + index * lineHeight);
    });
}

function drawShapePath(ctx, start, end, kind, preview = false) {
    const x = Math.min(start.x, end.x);
    const y = Math.min(start.y, end.y);
    const width = Math.abs(end.x - start.x);
    const height = Math.abs(end.y - start.y);

    if (preview) {
        ctx.setLineDash([8, 6]);
    }

    if (kind === 'shape_rect') {
        ctx.strokeRect(x, y, width, height);
    } else if (kind === 'shape_circle') {
        ctx.beginPath();
        ctx.ellipse(x + width / 2, y + height / 2, Math.max(width / 2, 1), Math.max(height / 2, 1), 0, 0, Math.PI * 2);
        ctx.stroke();
    }

    if (preview) {
        ctx.setLineDash([]);
    }
}

function drawElement(ctx, element, preview = false) {
    if (!element) {
        return;
    }

    const type = element.element_type || element.type || element.data?.tool || 'pen';
    const data = element.data || {};

    ctx.save();
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    if (type === 'text') {
        ctx.globalCompositeOperation = 'source-over';
        ctx.fillStyle = data.color || '#0f172a';
        ctx.font = `${data.fontSize || 18}px "Instrument Sans", sans-serif`;
        ctx.textBaseline = 'top';
        drawTextLines(ctx, data.text || '', data.x || 24, data.y || 24, data.lineHeight || 22);
        ctx.restore();
        return;
    }

    if (type === 'shape_rect' || type === 'shape_circle') {
        const start = data.start || data.points?.[0] || { x: data.x || 0, y: data.y || 0 };
        const end = data.end || data.points?.[data.points.length - 1] || start;
        ctx.globalCompositeOperation = 'source-over';
        ctx.strokeStyle = data.color || '#0f172a';
        ctx.lineWidth = data.lineWidth || 3;
        drawShapePath(ctx, start, end, type, preview);
        ctx.restore();
        return;
    }

    const points = Array.isArray(data.points) ? data.points : [];
    const strokeWidth = data.width || (type === 'eraser' || data.tool === 'eraser' ? 18 : 4);

    if (!points.length) {
        const point = data.start || data.end || { x: data.x || 0, y: data.y || 0 };
        points.push(point);
    }

    ctx.globalCompositeOperation = type === 'eraser' || data.tool === 'eraser' ? 'destination-out' : 'source-over';
    ctx.strokeStyle = data.color || '#0f172a';
    ctx.lineWidth = strokeWidth;
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);

    points.slice(1).forEach((point) => {
        ctx.lineTo(point.x, point.y);
    });

    if (points.length === 1) {
        ctx.lineTo(points[0].x + 0.1, points[0].y + 0.1);
    }

    if (preview) {
        ctx.setLineDash([6, 4]);
    }

    ctx.stroke();
    ctx.setLineDash([]);
    ctx.restore();
}

function renderWhiteboard(dom, state, previewElement = null) {
    if (!dom.canvas || !dom.context) {
        return;
    }

    dom.context.setTransform(1, 0, 0, 1, 0, 0);
    dom.context.clearRect(0, 0, dom.canvas.width, dom.canvas.height);

    state.whiteboardElements.forEach((element) => drawElement(dom.context, element));

    if (previewElement) {
        drawElement(dom.context, previewElement, true);
    }
}

function renderPointers(dom, state) {
    if (!dom.pointerLayer) {
        return;
    }

    dom.pointerLayer.innerHTML = '';

    state.pointerMap.forEach((pointer) => {
        const dot = document.createElement('div');
        dot.className = 'absolute pointer-events-none';
        dot.style.left = `${pointer.x_position}px`;
        dot.style.top = `${pointer.y_position}px`;

        const marker = document.createElement('div');
        marker.className = 'h-4 w-4 -translate-x-2 -translate-y-2 rounded-full bg-indigo-500/50 ring-4 ring-indigo-400/15';

        const label = document.createElement('span');
        label.className = 'absolute -mt-5 ml-3 whitespace-nowrap text-[10px] font-semibold text-indigo-700';
        label.textContent = pointer.user_name || 'User';

        dot.append(marker, label);
        dom.pointerLayer.appendChild(dot);
    });
}

function upsertPointer(dom, state, pointer) {
    if (!pointer) {
        return;
    }

    const key = String(pointer.user_id);
    const existing = state.pointerMap.get(key);

    if (existing?.timer) {
        clearTimeout(existing.timer);
    }

    const normalized = {
        user_id: Number(pointer.user_id),
        x_position: Number(pointer.x_position ?? pointer.x ?? 0),
        y_position: Number(pointer.y_position ?? pointer.y ?? 0),
        target_area: pointer.target_area || 'whiteboard',
        user_name: Number(pointer.user_id) === Number(state.userId)
            ? 'You'
            : (pointer.user_name || displayName(pointer.user)),
    };

    normalized.timer = setTimeout(() => {
        state.pointerMap.delete(key);
        renderPointers(dom, state);
    }, 12000);

    state.pointerMap.set(key, normalized);
    renderPointers(dom, state);
}

async function jsonRequest(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            ...classroomActionHeaders(),
            ...(options.headers || {}),
        },
    });

    if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
    }

    return response.json();
}

function connectEcho(dom, state, handlers) {
    if (!window.Echo || !state.sessionId) {
        return false;
    }

    const channel = window.Echo.private(`classroom.${state.sessionId}`);

        channel
            .listen('.whiteboard.element.created', handlers.onWhiteboardElementCreated)
            .listen('.whiteboard.element.updated', handlers.onWhiteboardElementUpdated)
            .listen('.whiteboard.element.deleted', handlers.onWhiteboardElementDeleted)
            .listen('.whiteboard.cleared', handlers.onWhiteboardCleared)
            .listen('.whiteboard.page.created', handlers.onWhiteboardPageCreated)
            .listen('.whiteboard.page.changed', handlers.onWhiteboardPageChanged)
            .listen('.whiteboard.page.deleted', handlers.onWhiteboardPageDeleted)
            .listen('.whiteboard.background.changed', handlers.onWhiteboardBackgroundChanged)
            .listen('.whiteboard.elements.moved', handlers.onWhiteboardElementsMoved)
            .listen('.whiteboard.snapshot.created', handlers.onWhiteboardSnapshotCreated)
            .listen('.whiteboard.permission.changed', handlers.onWhiteboardPermissionChanged)
            .listen('.classroom.message.sent', handlers.onChatMessage)
            .listen('.code.updated', handlers.onCodeUpdated)
        .listen('.code.saved', handlers.onCodeSaved)
        .listen('.pointer.moved', handlers.onPointerMoved)
        .listen('.participant.joined', handlers.onParticipantJoined)
        .listen('.participant.left', handlers.onParticipantLeft)
        .listen('.student.permission.changed', handlers.onStudentPermissionChanged)
        .listen('.classroom.mode.changed', handlers.onModeChanged)
        .listen('.classroom.status.changed', handlers.onStatusChanged)
        .listen('.classroom.textpad.updated', handlers.onTextPadUpdated);

    if (dom.activityStatus) {
        dom.activityStatus.textContent = 'Live sync connected';
    }

    if (dom.liveSyncStatus) {
        dom.liveSyncStatus.textContent = 'Live sync connected';
    }

    if (dom.sessionConnectionStatus) {
        dom.sessionConnectionStatus.textContent = 'Live sync connected';
    }

    return true;
}

function startClassroomWorkspace() {
    const config = createSessionConfig();
    if (!config) {
        return;
    }

    const dom = {
        whiteboardRoot: document.querySelector('[data-whiteboard-root]'),
        canvas: document.getElementById('whiteboard-canvas'),
        canvasContainer: document.getElementById('canvas-container'),
        pointerLayer: document.getElementById('pointers-layer'),
        chatMessages: document.getElementById('chat-messages'),
        chatInput: document.getElementById('chat-input'),
        chatSendBtn: document.getElementById('chat-send-btn'),
        participantsList: document.getElementById('participants-list'),
        participantCount: document.getElementById('participant-count'),
        codeEditor: document.getElementById('classroom-code-editor'),
        codeSaveBtn: document.getElementById('code-save-btn'),
        codeMoreBtn: document.getElementById('code-more-btn'),
        codeMoreMenu: document.getElementById('code-more-menu'),
        codeStatus: document.getElementById('code-status'),
        codeStatusInline: document.getElementById('code-status-inline'),
        codeSaveState: document.getElementById('code-save-state'),
        codeFileTabs: document.querySelector('[data-code-file-tabs]'),
        codeAddFileBtn: document.getElementById('code-add-file-btn'),
        codeRenameFileBtn: document.getElementById('code-rename-file-btn'),
        codeDeleteFileBtn: document.getElementById('code-delete-file-btn'),
        codeFileTitle: document.getElementById('code-file-title'),
        codeFileCount: document.getElementById('code-file-count'),
        codeLanguageLabel: document.getElementById('code-language-label'),
        codeLanguageLabelInline: document.getElementById('code-language-label-inline'),
        codeCursorStatus: document.getElementById('code-cursor-status'),
        codePreviewFrame: document.getElementById('code-preview-frame'),
        codePreviewEmptyState: document.querySelector('[data-preview-empty-state]'),
        codePreviewRefreshBtn: document.getElementById('code-preview-refresh-btn'),
        codeOutput: document.getElementById('code-output'),
        runPreviewBtn: document.getElementById('run-preview-btn'),
        resetCodeBtn: document.getElementById('reset-code-btn'),
        codeConsoleClearBtn: document.getElementById('code-console-clear-btn'),
        textpadEditor: document.getElementById('textpad-editor'),
        textpadSaveBtn: document.getElementById('textpad-save-btn'),
        textpadStatus: document.getElementById('textpad-status'),
        textpadWordCount: document.getElementById('textpad-word-count'),
        textpadLanguageLabel: document.getElementById('textpad-language-label'),
        textpadCollabStatus: document.getElementById('textpad-collab-status'),
        textpadSelectionStatus: document.getElementById('textpad-selection-status'),
        textpadTypingStatus: document.getElementById('textpad-typing-status'),
        textpadCommentCount: document.getElementById('textpad-comment-count'),
        textpadCommentsList: document.getElementById('textpad-comments-list'),
        textpadCommentInput: document.getElementById('textpad-comment-input'),
        textpadCommentAddBtn: document.getElementById('textpad-comment-add-btn'),
        textpadToolbarButtons: [...document.querySelectorAll('[data-textpad-command], [data-textpad-block], [data-textpad-action]')],
        sessionNotesEditor: document.getElementById('session-notes-editor'),
        sessionNotesStatus: document.getElementById('session-notes-status'),
        sessionNotesVisibility: document.getElementById('session-notes-visibility'),
        resourcesList: document.getElementById('resources-list'),
        resourcesCount: document.getElementById('resources-count'),
        resourceFileInput: document.getElementById('resource-file-input'),
        resourceUploadBtn: document.getElementById('resource-upload-btn'),
        resourceUploadStatus: document.getElementById('resource-upload-status'),
        activityFeed: document.getElementById('activity-feed'),
        activityStatus: document.getElementById('activity-status'),
        liveSyncStatus: document.getElementById('live-sync-status'),
        sessionConnectionStatus: document.getElementById('session-connection-status'),
        modeBadge: document.getElementById('classroom-mode-badge'),
        modeButtons: [...document.querySelectorAll('[data-mode-button]')],
        workspacePanels: [...document.querySelectorAll('[data-workspace-panel]')],
        modeVariantGroups: [...document.querySelectorAll('[data-mode-variant]')],
        toolButtons: [...document.querySelectorAll('[data-whiteboard-tool]')],
        colorButtons: [...document.querySelectorAll('[data-whiteboard-color]')],
        tabButtons: [...document.querySelectorAll('[data-classroom-tab-main]')],
        tabJumpButtons: [...document.querySelectorAll('[data-classroom-tab-jump]')],
        panels: [...document.querySelectorAll('[data-classroom-panel]')],
        clearBoardBtn: document.getElementById('clearBoardBtn'),
        saveSessionButtons: [...document.querySelectorAll('[id^="save-session-btn"]')],
        copyButtons: [...document.querySelectorAll('[data-copy-text]')],
        applyPermissionsBtn: document.getElementById('apply-permissions-btn'),
        permissionToggles: [...document.querySelectorAll('[data-permission-toggle]')],
        quickRoomActionButtons: [...document.querySelectorAll('[data-room-action]')],
    };

    if (!dom.canvas || !dom.canvasContainer) {
        return;
    }

    const state = {
        sessionId: config.sessionId,
        userId: config.userId,
        isTeacher: config.isTeacher,
        permissions: config.permissions,
        roomPermissions: config.roomPermissions,
        currentMode: config.currentMode,
        whiteboardState: deepClone(config.whiteboardState || defaultWhiteboardState()),
        codeFiles: normalizeCodeFiles(config.codeFiles || config.codeWorkspace?.files || {}, config.codeWorkspace?.files || {}),
        initialCodeFiles: {},
        activeCodeFileKey: normalizeCodeFileKey(config.codeActiveFileKey || config.codeWorkspace?.active_file_key || 'html'),
        codeLanguage: config.codeLanguage || 'plaintext',
        currentCode: config.codeDraft || '',
        currentTool: 'select',
        currentColor: '#0f172a',
        isDrawing: false,
        activeStroke: null,
        previewHasRun: false,
        previewAwaitingReady: false,
        previewHasError: false,
        previewNeedsRefresh: false,
        codeDirty: false,
        codeCursorLine: 1,
        codeCursorColumn: 1,
        whiteboardElements: [],
        pointerMap: new Map(),
        messageIds: new Set(),
        lastMessageId: 0,
        textPadSnapshot: sanitizeTextPadHtml(config.textPadContent || ''),
        textPadComments: normalizeTextPadComments(config.textPadComments || []),
        sessionNotes: config.sessionNotes || '',
        sessionResources: Array.isArray(config.sessionResources) ? config.sessionResources : [],
        codeSaveTimer: null,
        textPadSaveTimer: null,
        lastPointerSent: 0,
        hasEcho: false,
        isUpdatingRemoteCode: false,
        isUpdatingPermissions: false,
        textPadTypingTimer: null,
    };

    let whiteboardBoard = null;
    const codeEditorLanguageCompartment = new Compartment();
    const codeEditorEditableCompartment = new Compartment();
    let codeEditorView = null;

    dom.context = dom.canvas.getContext('2d');
    state.initialCodeFiles = deepClone(state.codeFiles);
    state.activeCodeFileKey = normalizeCodeFileKey(state.activeCodeFileKey);
    state.currentCode = String(state.codeFiles[state.activeCodeFileKey]?.content ?? state.currentCode ?? '');

    function activeCodeFile() {
        return state.codeFiles[state.activeCodeFileKey]
            || state.codeFiles.html
            || Object.values(state.codeFiles)[0]
            || defaultCodeFiles().html;
    }

    function codeEditorValue() {
        return codeEditorView ? codeEditorView.state.doc.toString() : String(state.currentCode ?? '');
    }

    function codeFileEntries() {
        return orderedCodeFileEntries(state.codeFiles);
    }

    function codeFileKeys() {
        return codeFileEntries().map(([key]) => key);
    }

    function ensureActiveCodeFile() {
        const keys = codeFileKeys();
        if (!keys.length) {
            state.codeFiles = deepClone(defaultCodeFiles());
            state.activeCodeFileKey = 'html';
            return state.codeFiles.html;
        }

        if (!keys.includes(state.activeCodeFileKey)) {
            state.activeCodeFileKey = keys.includes('html') ? 'html' : keys[0];
        }

        if (!state.codeFiles[state.activeCodeFileKey]) {
            state.codeFiles[state.activeCodeFileKey] = state.codeFiles[keys[0]] || defaultCodeFiles().html;
        }

        return state.codeFiles[state.activeCodeFileKey];
    }

    function refreshCodeEditorLayout() {
        window.requestAnimationFrame(() => {
            codeEditorView?.requestMeasure?.();
        });
    }

    function updateCodeCursorStatus(line = state.codeCursorLine, column = state.codeCursorColumn) {
        state.codeCursorLine = Number.isFinite(Number(line)) ? Math.max(1, Number(line)) : 1;
        state.codeCursorColumn = Number.isFinite(Number(column)) ? Math.max(1, Number(column)) : 1;

        if (dom.codeCursorStatus) {
            dom.codeCursorStatus.textContent = `Ln ${state.codeCursorLine}, Col ${state.codeCursorColumn}`;
        }
    }

    function setCodeEditorErrorState(hasError = false) {
        state.previewHasError = Boolean(hasError);
        dom.codeEditor?.classList.toggle('cb-code-editor-error', state.previewHasError);
    }

    function setPreviewEmptyStateVisible(visible = true) {
        if (!dom.codePreviewEmptyState) {
            return;
        }

        dom.codePreviewEmptyState.classList.toggle('hidden', !visible);
    }

    function clearCodeConsole(message = 'Preview output appears here.') {
        if (dom.codeOutput) {
            dom.codeOutput.textContent = message;
        }

        setCodeEditorErrorState(false);
    }

    function updateCodeFileCount() {
        if (dom.codeFileCount) {
            const count = Object.keys(state.codeFiles || {}).length;
            dom.codeFileCount.textContent = `${count} file${count === 1 ? '' : 's'}`;
        }
    }

    function setCodeSaveState(text) {
        if (dom.codeSaveState) {
            dom.codeSaveState.textContent = text;
        }
    }

    function setCodeEditorDocument(content, file = activeCodeFile(), { preserveSelection = true, remote = false } = {}) {
        const activeFile = file || activeCodeFile();
        const nextContent = String(content ?? '');
        state.currentCode = nextContent;
        state.codeLanguage = activeFile?.language || state.codeLanguage || 'plaintext';
        state.codeFiles[state.activeCodeFileKey] = state.codeFiles[state.activeCodeFileKey] || activeFile;
        state.codeFiles[state.activeCodeFileKey].content = nextContent;

        if (!codeEditorView) {
            return;
        }

        if (remote) {
            state.isUpdatingRemoteCode = true;
        }

        try {
            codeEditorView.dispatch({
                changes: {
                    from: 0,
                    to: codeEditorView.state.doc.length,
                    insert: nextContent,
                },
                selection: preserveSelection ? codeEditorView.state.selection : undefined,
                effects: [
                    codeEditorLanguageCompartment.reconfigure(classroomLanguageForFile(activeFile)),
                    codeEditorEditableCompartment.reconfigure(EditorView.editable.of(canUseCodeEditor(state))),
                ],
            });
        } finally {
            if (remote) {
                state.isUpdatingRemoteCode = false;
            }
        }
    }

    function syncCodeEditorStateFromView() {
        const activeFile = activeCodeFile();
        const content = codeEditorValue();
        state.currentCode = content;
        state.codeFiles[state.activeCodeFileKey] = state.codeFiles[state.activeCodeFileKey] || activeFile;
        state.codeFiles[state.activeCodeFileKey].content = content;
        state.codeLanguage = state.codeFiles[state.activeCodeFileKey].language || state.codeLanguage || 'plaintext';
        return state.codeFiles[state.activeCodeFileKey];
    }

    function initCodeEditor() {
        if (!dom.codeEditor) {
            return;
        }

        const activeFile = activeCodeFile();

        codeEditorView = new EditorView({
            state: EditorState.create({
                doc: String(activeFile?.content ?? state.currentCode ?? ''),
                extensions: [
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
                    CLASSROOM_CODE_THEME,
                    codeEditorLanguageCompartment.of(classroomLanguageForFile(activeFile)),
                    codeEditorEditableCompartment.of(EditorView.editable.of(canUseCodeEditor(state))),
                    EditorView.updateListener.of((update) => {
                        if (state.isUpdatingRemoteCode) {
                            return;
                        }

                        if (update.docChanged || update.selectionSet) {
                            const mainSelection = update.state.selection.main;
                            const line = update.state.doc.lineAt(mainSelection.head);
                            updateCodeCursorStatus(line.number, mainSelection.head - line.from + 1);
                        }

                        if (!update.docChanged) {
                            return;
                        }

                        syncCodeEditorStateFromView();
                        if (state.previewHasRun) {
                            state.previewNeedsRefresh = true;
                        }
                        state.codeDirty = true;
                        setCodeSaveState('Unsaved changes');
                        if (dom.codeStatus) {
                            dom.codeStatus.textContent = 'Typing...';
                        }
                        if (dom.codeStatusInline) {
                            dom.codeStatusInline.textContent = 'Typing...';
                        }
                        scheduleCodeSave();
                    }),
                ],
            }),
            parent: dom.codeEditor,
        });

        refreshCodeEditorLayout();
    }

    function updateToolButtons(tool) {
        dom.toolButtons.forEach((button) => {
            const active = button.dataset.whiteboardTool === tool;
            button.classList.toggle('bg-slate-950', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('border-slate-950', active);
            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-slate-500', !active);
        });
    }

    function updateColorButtons(color) {
        dom.colorButtons.forEach((button) => {
            const active = button.dataset.whiteboardColor === color;
            button.classList.toggle('ring-4', active);
            button.classList.toggle('ring-slate-950/20', active);
        });
    }

    function getCodeFileTabButtons() {
        return dom.codeFileTabs ? [...dom.codeFileTabs.querySelectorAll('[data-code-file-tab]')] : [];
    }

    function renderCodeTabs() {
        if (!dom.codeFileTabs) {
            return;
        }

        const entries = codeFileEntries();
        dom.codeFileTabs.innerHTML = '';

        entries.forEach(([key, file]) => {
            const button = document.createElement('button');
            const isActive = key === state.activeCodeFileKey;
            button.type = 'button';
            button.dataset.codeFileTab = key;
            button.className = `cb-ide-tab shrink-0 ${isActive ? 'cb-ide-tab-active' : 'cb-ide-tab-inactive'}`;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            button.title = `${file.filename || key} | ${codeLabelForLanguage(file.language || '')}`;
            const label = document.createElement('span');
            label.className = 'inline-flex items-center gap-2';

            const dot = document.createElement('span');
            dot.className = `h-2.5 w-2.5 rounded-full ${isActive ? 'bg-white' : 'bg-slate-300'}`;

            const filename = document.createElement('span');
            filename.className = 'max-w-[9rem] truncate';
            filename.textContent = file.filename || key;

            label.append(dot, filename);
            button.appendChild(label);
            dom.codeFileTabs.appendChild(button);
        });
    }

    function syncActiveCodeFile() {
        const activeFile = ensureActiveCodeFile();
        const key = state.activeCodeFileKey;
        state.codeFiles[key] = state.codeFiles[key] || activeFile;
        state.codeFiles[key].content = codeEditorValue();
        state.currentCode = state.codeFiles[key].content;
        state.codeLanguage = state.codeFiles[key].language || state.codeLanguage || 'plaintext';
        return state.codeFiles[key];
    }

    function renderCodeWorkspace(status = null) {
        const activeFile = ensureActiveCodeFile();
        const baseStatus = status || `Editing ${activeFile.filename || 'index.html'}`;
        const displayStatus = status && state.previewHasRun && state.previewNeedsRefresh
            ? `${baseStatus} | Preview stale`
            : baseStatus;

        renderCodeTabs();
        updateCodeFileCount();

        if (codeEditorView && codeEditorValue() !== String(activeFile.content ?? '')) {
            setCodeEditorDocument(activeFile.content ?? '', activeFile, { preserveSelection: true, remote: true });
        }

        if (dom.codeFileTitle) {
            dom.codeFileTitle.textContent = activeFile.filename || 'index.html';
        }

        if (dom.codeLanguageLabel) {
            dom.codeLanguageLabel.textContent = String(activeFile.label || activeFile.language || 'plaintext').toUpperCase();
        }

        if (dom.codeStatus) {
            dom.codeStatus.textContent = displayStatus;
        }

        if (dom.codeStatusInline) {
            dom.codeStatusInline.textContent = displayStatus;
        }

        if (dom.codeLanguageLabelInline) {
            dom.codeLanguageLabelInline.textContent = String(activeFile.label || activeFile.language || 'plaintext').toUpperCase();
        }

        if (dom.codeCursorStatus) {
            updateCodeCursorStatus(state.codeCursorLine, state.codeCursorColumn);
        }

        if (!status && state.previewHasRun) {
            const previewState = state.previewHasError
                ? 'Preview error'
                : (state.previewNeedsRefresh ? 'Preview stale' : 'Preview ready');
            if (dom.codeStatus) {
                dom.codeStatus.textContent = previewState;
            }
            if (dom.codeStatusInline) {
                dom.codeStatusInline.textContent = previewState;
            }
        }
    }

    function runCodePreview() {
        if (!dom.codePreviewFrame) {
            return;
        }

        syncActiveCodeFile();
        state.previewHasRun = true;
        state.previewAwaitingReady = true;
        state.previewHasError = false;
        state.previewNeedsRefresh = false;
        setCodeEditorErrorState(false);
        setPreviewEmptyStateVisible(false);
        clearCodeConsole('Preview output appears here.');
        dom.codePreviewFrame.srcdoc = buildPreviewDocument(state.codeFiles);

        if (dom.codeStatus) {
            dom.codeStatus.textContent = 'Preview running';
        }

        if (dom.codeStatusInline) {
            dom.codeStatusInline.textContent = 'Preview running';
        }
    }

    function appendConsoleLine(message, tone = 'info') {
        if (!dom.codeOutput) {
            return;
        }

        const current = dom.codeOutput.textContent || '';
        const prefix = tone === 'error' ? 'Error' : tone === 'warn' ? 'Warning' : '';
        const body = String(message ?? '').trim();
        const line = `[${formatClock()}] ${prefix ? `${prefix}: ` : ''}${body}`;
        const lines = current && current !== 'Preview output appears here.' ? current.split('\n') : [];
        lines.unshift(line);
        dom.codeOutput.textContent = lines.slice(0, 8).join('\n');

        if (tone === 'error') {
            state.previewHasError = true;
            setCodeEditorErrorState(true);
            if (dom.codeStatus) {
                dom.codeStatus.textContent = 'Preview error';
            }
            if (dom.codeStatusInline) {
                dom.codeStatusInline.textContent = 'Preview error';
            }
        } else if (tone === 'info' && String(message ?? '').toLowerCase().includes('preview updated successfully')) {
            state.previewAwaitingReady = false;
        }
    }

    function switchCodeFile(key, { announce = true } = {}) {
        syncActiveCodeFile();
        const normalizedKey = normalizeCodeFileKey(key);
        state.activeCodeFileKey = normalizedKey;
        const activeFile = state.codeFiles[normalizedKey] || state.codeFiles.html || Object.values(state.codeFiles)[0] || defaultCodeFiles().html;
        state.currentCode = String(activeFile.content ?? '');
        state.codeLanguage = activeFile.language || 'plaintext';
        if (state.previewHasRun) {
            state.previewNeedsRefresh = true;
        }

        setCodeEditorDocument(state.currentCode, activeFile, { preserveSelection: true, remote: true });

        renderCodeWorkspace(announce ? `Editing ${activeFile.filename || normalizedKey}` : null);
        if (canUseCodeEditor(state)) {
            scheduleCodeSave();
        }
    }

    function resetCodeWorkspace() {
        state.codeFiles = deepClone(state.initialCodeFiles || defaultCodeFiles()) || defaultCodeFiles();
        state.activeCodeFileKey = state.codeFiles.html ? 'html' : Object.keys(state.codeFiles)[0] || 'html';
        state.previewHasRun = false;
        state.previewAwaitingReady = false;
        state.previewHasError = false;
        state.previewNeedsRefresh = false;
        state.codeDirty = false;
        setPreviewEmptyStateVisible(true);
        clearCodeConsole('Preview output appears here.');
        renderCodeWorkspace('Starter code restored.');
        if (dom.codePreviewFrame) {
            dom.codePreviewFrame.removeAttribute('srcdoc');
        }
        setCodeSaveState('Synced');
    }

    function createCodeFile() {
        const filename = clampText(window.prompt('New file name (for example: lesson.js)', 'lesson.js'));
        if (!filename) {
            return;
        }

        const keys = codeFileKeys();
        const key = uniqueCodeFileKey(normalizeCodeFileKey(filename), keys);
        const template = codeFileTemplateFromFilename(filename);

        state.codeFiles[key] = {
            ...template,
            filename,
            sort_order: keys.length,
        };
        state.activeCodeFileKey = key;
        if (state.previewHasRun) {
            state.previewNeedsRefresh = true;
        }
        state.codeDirty = true;
        setCodeSaveState('Unsaved changes');
        setCodeEditorDocument('', state.codeFiles[key], { preserveSelection: true, remote: false });
        renderCodeWorkspace(`Created ${filename}`);
        saveCode({ persist: true }).catch((error) => {
            console.error(error);
            appendSystemLine(dom, 'New file could not be saved.');
        });
    }

    function renameActiveCodeFile() {
        const key = state.activeCodeFileKey;
        const file = state.codeFiles[key];
        if (!file) {
            return;
        }

        const nextName = clampText(window.prompt('Rename file', file.filename || key));
        if (!nextName) {
            return;
        }

        const nextLanguage = codeLanguageFromFilename(nextName, file.language || 'javascript');
        state.codeFiles[key] = {
            ...file,
            filename: nextName,
            language: nextLanguage,
            label: codeLabelForLanguage(nextLanguage),
        };

        if (state.previewHasRun) {
            state.previewNeedsRefresh = true;
        }
        state.codeDirty = true;
        setCodeSaveState('Unsaved changes');
        setCodeEditorDocument(codeEditorValue(), state.codeFiles[key], { preserveSelection: true, remote: false });
        renderCodeWorkspace(`Renamed to ${nextName}`);
        saveCode({ persist: true }).catch((error) => {
            console.error(error);
            appendSystemLine(dom, 'File rename could not be saved.');
        });
    }

    function deleteActiveCodeFile() {
        const key = state.activeCodeFileKey;
        const file = state.codeFiles[key];
        const keys = codeFileKeys();

        if (!file || keys.length <= 1) {
            window.alert('The workspace needs at least one file.');
            return;
        }

        if (!window.confirm(`Delete ${file.filename || key}?`)) {
            return;
        }

        delete state.codeFiles[key];

        const remainingKeys = codeFileKeys();
        state.activeCodeFileKey = remainingKeys.includes('html') ? 'html' : remainingKeys[0];
        const nextFile = ensureActiveCodeFile();
        if (state.previewHasRun) {
            state.previewNeedsRefresh = true;
        }
        state.codeDirty = true;
        setCodeSaveState('Unsaved changes');

        setCodeEditorDocument(nextFile.content || '', nextFile, { preserveSelection: true, remote: false });
        renderCodeWorkspace(`Deleted ${file.filename || key}`);
        saveCode({ persist: true }).catch((error) => {
            console.error(error);
            appendSystemLine(dom, 'File delete could not be saved.');
        });
    }

    function resizeCanvas() {
        const rect = dom.canvasContainer.getBoundingClientRect();
        dom.canvas.width = Math.max(1, Math.floor(rect.width));
        dom.canvas.height = Math.max(1, Math.floor(rect.height));
        dom.context.lineCap = 'round';
        dom.context.lineJoin = 'round';
        whiteboardBoard?.resize?.();
    }

    function renderRoomPermissions(permissions = state.roomPermissions) {
        const source = permissions || {};

        if (state.isTeacher && dom.permissionToggles.length) {
            dom.permissionToggles.forEach((toggle) => {
                toggle.checked = Boolean(source[toggle.dataset.permissionKey]);
            });
        }
    }

    function syncInteractiveControls() {
        const canChat = state.isTeacher || Boolean(state.permissions.chat ?? state.roomPermissions.chat);

        if (codeEditorView) {
            codeEditorView.dispatch({
                effects: codeEditorEditableCompartment.reconfigure(EditorView.editable.of(canUseCodeEditor(state))),
            });
            dom.codeEditor.classList.toggle('opacity-70', !canUseCodeEditor(state));
            dom.codeEditor.classList.toggle('cursor-not-allowed', !canUseCodeEditor(state));
        }

        if (dom.codeSaveBtn) {
            dom.codeSaveBtn.disabled = !canUseCodeEditor(state);
        }

        getCodeFileTabButtons().forEach((button) => {
            button.disabled = !canUseCodeEditor(state);
            button.classList.toggle('opacity-60', !canUseCodeEditor(state));
        });

        if (dom.codeAddFileBtn) {
            dom.codeAddFileBtn.disabled = !canUseCodeEditor(state);
            dom.codeAddFileBtn.classList.toggle('opacity-60', !canUseCodeEditor(state));
        }

        if (dom.codeRenameFileBtn) {
            dom.codeRenameFileBtn.disabled = !canUseCodeEditor(state);
            dom.codeRenameFileBtn.classList.toggle('opacity-60', !canUseCodeEditor(state));
        }

        if (dom.codeDeleteFileBtn) {
            dom.codeDeleteFileBtn.disabled = !canUseCodeEditor(state);
            dom.codeDeleteFileBtn.classList.toggle('opacity-60', !canUseCodeEditor(state));
        }

        if (dom.runPreviewBtn) {
            dom.runPreviewBtn.disabled = !canUseCodeEditor(state);
        }

        if (dom.codePreviewRefreshBtn) {
            dom.codePreviewRefreshBtn.disabled = !canUseCodeEditor(state);
            dom.codePreviewRefreshBtn.classList.toggle('opacity-60', !canUseCodeEditor(state));
        }

        if (dom.resetCodeBtn) {
            dom.resetCodeBtn.disabled = !canUseCodeEditor(state);
        }

        if (dom.textpadEditor) {
            dom.textpadEditor.contentEditable = canUseTextPad(state) ? 'true' : 'false';
            dom.textpadEditor.classList.toggle('opacity-70', !canUseTextPad(state));
            dom.textpadEditor.classList.toggle('cursor-not-allowed', !canUseTextPad(state));
        }

        if (dom.textpadSaveBtn) {
            dom.textpadSaveBtn.disabled = !canUseTextPad(state);
        }

        dom.textpadToolbarButtons.forEach((button) => {
            button.disabled = !canUseTextPad(state);
            button.classList.toggle('opacity-60', !canUseTextPad(state));
        });

        if (dom.textpadCommentAddBtn) {
            dom.textpadCommentAddBtn.disabled = !state.isTeacher;
            dom.textpadCommentAddBtn.classList.toggle('opacity-60', !state.isTeacher);
        }

        if (dom.textpadCommentInput) {
            dom.textpadCommentInput.disabled = !state.isTeacher;
            dom.textpadCommentInput.classList.toggle('opacity-70', !state.isTeacher);
        }

        updateTextPadCollabState(dom, state);

        if (dom.chatInput) {
            dom.chatInput.disabled = !canChat;
            dom.chatInput.classList.toggle('opacity-70', !canChat);
        }

        if (dom.chatSendBtn) {
            dom.chatSendBtn.disabled = !canChat;
        }

        if (dom.clearBoardBtn) {
            dom.clearBoardBtn.disabled = !state.isTeacher;
        }

        if (dom.applyPermissionsBtn) {
            dom.applyPermissionsBtn.disabled = !canManagePermissions(state);
        }

        whiteboardBoard?.refreshAccess?.();
    }

    function applyRoomPermissions(permissions = {}) {
        state.roomPermissions = {
            ...state.roomPermissions,
            ...permissions,
        };

        if (!state.isTeacher) {
            state.permissions = {
                ...state.permissions,
                ...state.roomPermissions,
            };
        }

        renderRoomPermissions(state.roomPermissions);
        syncInteractiveControls();
    }

    function setCodeDraft(code, { source = 'room', status = null, files = null, activeFileKey = null } = {}) {
        if (files) {
            state.codeFiles = normalizeCodeFiles(files, state.codeFiles);
            state.initialCodeFiles = deepClone(state.initialCodeFiles || state.codeFiles);
        }

        const key = normalizeCodeFileKey(activeFileKey || state.activeCodeFileKey);
        state.activeCodeFileKey = key;
        state.codeFiles[key] = state.codeFiles[key] || defaultCodeFiles()[key];
        state.codeFiles[key].content = String(code ?? state.codeFiles[key].content ?? '');
        state.currentCode = state.codeFiles[key].content;
        state.codeLanguage = state.codeFiles[key].language || state.codeLanguage || 'plaintext';

        setCodeEditorDocument(state.currentCode, state.codeFiles[key], {
            preserveSelection: true,
            remote: source !== 'self',
        });

        if (state.previewHasRun) {
            state.previewNeedsRefresh = true;
        }

        renderCodeWorkspace(status || (source === 'self' ? 'Synced' : `Updated by ${source}`));
    }

    function collectRoomPermissionsFromUi() {
        const permissions = {
            draw: false,
            type: false,
            chat: false,
            pointer: false,
            code: false,
            download: false,
            whiteboard_draw: false,
            whiteboard_text: false,
            whiteboard_shapes: false,
            whiteboard_images: false,
            whiteboard_erase: false,
            whiteboard_pointer: false,
            whiteboard_comments: false,
            whiteboard_page_switch: false,
            whiteboard_page_create: false,
            whiteboard_object_move: false,
            whiteboard_download: false,
            whiteboard_lock_board: false,
            whiteboard_follow_teacher_page: false,
            whiteboard_follow_teacher_viewport: false,
        };

        dom.permissionToggles.forEach((toggle) => {
            const key = toggle.dataset.permissionKey;
            if (key in permissions) {
                permissions[key] = Boolean(toggle.checked);
            }
        });

        return permissions;
    }

    function applyWhiteboardElement(element, { fromServer = false } = {}) {
        if (!element) {
            return;
        }

        const normalized = {
            ...element,
            id: element.id ?? `${Date.now()}-${Math.random().toString(36).slice(2)}`,
            element_type: element.element_type || element.type || element.data?.tool || 'pen',
            data: element.data || {},
        };

        const existingIndex = state.whiteboardElements.findIndex((item) => String(item.id) === String(normalized.id));

        if (existingIndex >= 0) {
            state.whiteboardElements.splice(existingIndex, 1, normalized);
        } else {
            state.whiteboardElements.push(normalized);
        }

        state.whiteboardElements = sortElements(state.whiteboardElements);
        whiteboardBoard?.applyRemoteElement?.(normalized).catch((error) => console.error(error));

        if (fromServer) {
            appendSystemLine(dom, `${normalized.user_name || 'Someone'} updated the shared whiteboard.`);
        }
    }

    async function loadWhiteboard() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/whiteboard`, { method: 'GET' });
        state.whiteboardState = data.whiteboard_state || state.whiteboardState || defaultWhiteboardState();
        state.whiteboardElements = sortElements(data.elements || []);
        if (data.whiteboard?.snapshots) {
            whiteboardBoard?.setSnapshots?.(data.whiteboard.snapshots || []);
        }
        if (whiteboardBoard?.setWhiteboardState) {
            whiteboardBoard.setWhiteboardState(state.whiteboardState);
        }
        if (whiteboardBoard) {
            await whiteboardBoard.loadElements(state.whiteboardElements);
        }
    }

    async function loadWhiteboardSnapshots() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/whiteboard/snapshots`, { method: 'GET' });
        whiteboardBoard?.setSnapshots?.(data.snapshots || []);
    }

    async function loadMessages({ sinceId = null, replace = false } = {}) {
        const url = new URL(`/api/classroom/${state.sessionId}/messages`, window.location.origin);
        if (sinceId) {
            url.searchParams.set('since_id', String(sinceId));
        }

        const data = await jsonRequest(url.toString(), { method: 'GET' });
        const messages = data.messages || [];

        if (replace) {
            dom.chatMessages.innerHTML = '';
            state.messageIds.clear();
            state.lastMessageId = 0;
        }

        messages.forEach((message) => renderMessage(dom, state, message));
    }

    async function loadParticipants() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/participants`, { method: 'GET' });
        renderParticipants(dom, data.participants || [], state);
    }

    async function loadPointers() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/pointers`, { method: 'GET' });
        const selfPointer = state.pointerMap.get(String(state.userId))
            ? { ...state.pointerMap.get(String(state.userId)) }
            : null;

        state.pointerMap.forEach((pointer) => {
            if (pointer.timer) {
                clearTimeout(pointer.timer);
            }
        });
        state.pointerMap.clear();

        (data.pointers || []).forEach((pointer) => {
            upsertPointer(dom, state, pointer);
        });

        if (selfPointer) {
            upsertPointer(dom, state, selfPointer);
        }

        renderPointers(dom, state);
    }

    async function loadTextPad() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/textpad`, { method: 'GET' });
        state.textPadSnapshot = sanitizeTextPadHtml(data.content || '');
        state.textPadComments = normalizeTextPadComments(data.comments || state.textPadComments);

        setTextPadHtml(dom, state.textPadSnapshot);
        updateTextPadWordCount(dom, state.textPadSnapshot);
        renderTextPadComments(dom, state);
    }

    async function loadCode() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/code`, { method: 'GET' });
        const workspace = data.workspace || {};
        const files = normalizeCodeFiles(data.files || workspace.files || {}, state.codeFiles);
        state.codeFiles = files;
        state.initialCodeFiles = deepClone(files);
        state.activeCodeFileKey = normalizeCodeFileKey(data.active_file_key || workspace.active_file_key || state.activeCodeFileKey);
        state.codeLanguage = data.language || state.codeLanguage || files[state.activeCodeFileKey]?.language || 'plaintext';

        setCodeDraft(data.code || files[state.activeCodeFileKey]?.content || '', {
            source: 'room',
            status: data.saved_at ? `Saved ${formatClock(data.saved_at)}` : 'Synced',
            files,
            activeFileKey: state.activeCodeFileKey,
        });
        state.previewHasRun = false;
        state.previewAwaitingReady = false;
        state.previewHasError = false;
        state.previewNeedsRefresh = false;
        state.codeDirty = false;
        setPreviewEmptyStateVisible(true);
        if (dom.codePreviewFrame) {
            dom.codePreviewFrame.removeAttribute('srcdoc');
        }
        setCodeSaveState('Synced');
    }

    async function loadSessionState() {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/state`, { method: 'GET' });
        updateModeUi(dom, state, data.mode || state.currentMode);
        state.codeLanguage = data.code_language || state.codeLanguage;
        state.sessionNotes = data.session_notes ?? state.sessionNotes ?? '';
        state.sessionResources = Array.isArray(data.resources) ? data.resources : state.sessionResources;
        state.textPadComments = normalizeTextPadComments(data.textpad_comments || state.textPadComments);
        state.whiteboardState = data.whiteboard_state || state.whiteboardState || defaultWhiteboardState();

        if (dom.sessionNotesEditor && !dom.sessionNotesEditor.value.trim()) {
            dom.sessionNotesEditor.value = state.sessionNotes;
        }

        renderTextPadComments(dom, state);

        if (whiteboardBoard?.setWhiteboardState) {
            whiteboardBoard.setWhiteboardState(state.whiteboardState);
        }

        if (data.student_permissions) {
            applyRoomPermissions(data.student_permissions);
        }

        if (dom.activityStatus) {
            dom.activityStatus.textContent = data.status === 'live' ? 'Live session' : (data.status === 'ended' ? 'Session ended' : 'Waiting room');
        }

        if (dom.liveSyncStatus) {
            dom.liveSyncStatus.textContent = data.status === 'live' ? 'Live session' : (data.status === 'ended' ? 'Session ended' : 'Waiting room');
        }

        if (dom.sessionConnectionStatus) {
            dom.sessionConnectionStatus.textContent = data.status === 'live' ? 'Live session' : (data.status === 'ended' ? 'Session ended' : 'Waiting room');
        }

        if (dom.codeOutput && dom.codeOutput.textContent === 'Preview output appears here.') {
            dom.codeOutput.textContent = data.status === 'live'
                ? 'Press Run Preview to render the lesson.'
                : 'Start the session and press Run Preview to see the lesson.';
        }
    }

    async function submitWhiteboardElement(element) {
        return jsonRequest(`/api/classroom/${state.sessionId}/whiteboard`, {
            method: 'POST',
            body: JSON.stringify(element),
        });
    }

    async function deleteWhiteboardElement(elementId) {
        return jsonRequest(`/api/classroom/${state.sessionId}/whiteboard`, {
            method: 'POST',
            body: JSON.stringify({
                action: 'delete',
                id: elementId,
            }),
        });
    }

    async function clearWhiteboard({ pageKey = null } = {}) {
        const nextPageKey = pageKey || whiteboardBoard?.getState?.()?.active_page || state.whiteboardState?.active_page || 'page-1';
        await jsonRequest(`/api/classroom/${state.sessionId}/whiteboard`, {
            method: 'DELETE',
            body: JSON.stringify({ page_key: nextPageKey }),
        });
        state.whiteboardElements = state.whiteboardElements.filter((element) => String(element.data?.page_key || nextPageKey) !== String(nextPageKey));
        whiteboardBoard?.clearRemote?.(nextPageKey);
        appendSystemLine(dom, nextPageKey ? `Page ${nextPageKey} was cleared.` : 'The whiteboard was cleared.');
    }

    async function createWhiteboardSnapshot({ name = null, reason = null, pageKey = null } = {}) {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/whiteboard/snapshots`, {
            method: 'POST',
            body: JSON.stringify({
                name,
                reason,
                page_key: pageKey,
            }),
        });

        await loadWhiteboardSnapshots();
        return data;
    }

    async function restoreWhiteboardSnapshot(snapshotId) {
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/whiteboard/snapshots/${snapshotId}/restore`, {
            method: 'POST',
        });

        if (data.whiteboard_state) {
            state.whiteboardState = data.whiteboard_state;
            whiteboardBoard?.setWhiteboardState?.(state.whiteboardState);
        }

        await loadWhiteboard();
        await loadWhiteboardSnapshots();
        appendSystemLine(dom, 'Snapshot restored.');
        return data;
    }

    async function sendChatMessage() {
        const message = clampText(dom.chatInput?.value);
        if (!message) {
            return;
        }

        const data = await jsonRequest(`/api/classroom/${state.sessionId}/messages`, {
            method: 'POST',
            body: JSON.stringify({ message }),
        });

        if (dom.chatInput) {
            dom.chatInput.value = '';
        }

        if (data.message) {
            renderMessage(dom, state, data.message);
        }
    }

    async function saveTextPad() {
        if (!dom.textpadEditor) {
            return;
        }

        if (state.textPadSaveTimer) {
            clearTimeout(state.textPadSaveTimer);
            state.textPadSaveTimer = null;
        }

        const currentContent = getTextPadHtml(dom);
        const content = isTextPadBlankHtml(currentContent) ? '' : currentContent;
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/textpad`, {
            method: 'POST',
            body: JSON.stringify({
                content,
                comments: state.textPadComments,
            }),
        });

        state.textPadSnapshot = sanitizeTextPadHtml(data.content || content);
        state.textPadComments = normalizeTextPadComments(data.comments || state.textPadComments);
        updateTextPadWordCount(dom, state.textPadSnapshot);
        renderTextPadComments(dom, state);
        if (dom.textpadStatus) {
            dom.textpadStatus.textContent = 'Saved';
        }
    }

    async function saveCode({ persist = false } = {}) {
        if (!codeEditorView) {
            return;
        }

        if (state.codeSaveTimer) {
            clearTimeout(state.codeSaveTimer);
            state.codeSaveTimer = null;
        }

        syncActiveCodeFile();
        const code = state.codeFiles[state.activeCodeFileKey]?.content ?? state.currentCode ?? '';
        const activeFile = state.codeFiles[state.activeCodeFileKey] || defaultCodeFiles()[state.activeCodeFileKey];
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/code`, {
            method: 'POST',
            body: JSON.stringify({
                code,
                language: activeFile.language || state.codeLanguage,
                active_file_key: state.activeCodeFileKey,
                code_tabs: state.codeFiles,
                persist,
            }),
        });

        state.currentCode = data.code || code;
        state.codeLanguage = data.language || state.codeLanguage;
        if (data.files && Object.keys(data.files).length) {
            state.codeFiles = normalizeCodeFiles(data.files, state.codeFiles);
            state.initialCodeFiles = deepClone(state.initialCodeFiles || state.codeFiles);
        }
        state.activeCodeFileKey = normalizeCodeFileKey(data.active_file_key || state.activeCodeFileKey);
        const savedLabel = data.saved_at ? `Saved ${formatClock(data.saved_at)}` : 'Synced';
        const previewLabel = state.previewHasRun
            ? (state.previewNeedsRefresh ? 'Preview stale' : 'Preview ready')
            : null;
        renderCodeWorkspace(previewLabel ? `${savedLabel} | ${previewLabel}` : savedLabel);
        if (dom.codeStatus) {
            dom.codeStatus.textContent = previewLabel ? `${savedLabel} | ${previewLabel}` : savedLabel;
        }
        if (dom.codeStatusInline) {
            dom.codeStatusInline.textContent = previewLabel ? `${savedLabel} | ${previewLabel}` : savedLabel;
        }

        state.codeDirty = false;
        setCodeSaveState('Saved');
    }

    async function saveSessionSnapshot({ whiteboardState = null } = {}) {
        if (state.textPadSaveTimer) {
            clearTimeout(state.textPadSaveTimer);
            state.textPadSaveTimer = null;
        }

        if (state.codeSaveTimer) {
            clearTimeout(state.codeSaveTimer);
            state.codeSaveTimer = null;
        }

        if (dom.textpadEditor && canUseTextPad(state)) {
            await saveTextPad();
        }

        if (canUseCodeEditor(state)) {
            await saveCode({ persist: false });
        }

        const sessionNotes = clampText(dom.sessionNotesEditor?.value || state.sessionNotes || '');
        state.sessionNotes = sessionNotes;

        const nextWhiteboardState = whiteboardState || whiteboardBoard?.getState?.() || state.whiteboardState || defaultWhiteboardState();
        state.whiteboardState = nextWhiteboardState;

        const data = await jsonRequest(`/api/classroom/${state.sessionId}/save-session`, {
            method: 'POST',
            body: JSON.stringify({
                session_notes: sessionNotes,
                resources: state.sessionResources || [],
                whiteboard_state: nextWhiteboardState,
            }),
        });

        if (dom.codeStatus) {
            dom.codeStatus.textContent = data.saved_at ? `Session saved ${formatClock(data.saved_at)}` : 'Session saved';
        }

        appendSystemLine(dom, `Session snapshot saved (${data.whiteboard_count || 0} whiteboard items).`);
    }

    async function applyPermissions() {
        if (!canManagePermissions(state)) {
            return;
        }

        const permissions = collectRoomPermissionsFromUi();
        const data = await jsonRequest(`/api/classroom/${state.sessionId}/permissions`, {
            method: 'POST',
            body: JSON.stringify({ permissions }),
        });

        applyRoomPermissions(data.permissions || permissions);
        appendSystemLine(dom, 'Student permissions were updated for the classroom.');
    }

    async function changeMode(mode) {
        if (!state.isTeacher) {
            return;
        }

        const data = await jsonRequest(`/api/classroom/${state.sessionId}/mode`, {
            method: 'POST',
            body: JSON.stringify({ mode }),
        });

        updateModeUi(dom, state, data.mode || mode);
        appendSystemLine(dom, `Mode changed to ${MODE_LABELS[data.mode || mode] || data.mode || mode}.`);
    }

    function sendPointerPoint(point, targetArea = 'whiteboard') {
        if (!point || !canUsePointer(state)) {
            return;
        }

        const now = Date.now();
        if (now - state.lastPointerSent < 350) {
            return;
        }

        state.lastPointerSent = now;

        jsonRequest(`/api/classroom/${state.sessionId}/pointer`, {
            method: 'POST',
            body: JSON.stringify({
                x_position: Number(point.x ?? 0),
                y_position: Number(point.y ?? 0),
                target_area: targetArea,
            }),
        }).catch((error) => console.error(error));

        upsertPointer(dom, state, {
            user_id: state.userId,
            x_position: Number(point.x ?? 0),
            y_position: Number(point.y ?? 0),
            user_name: 'You',
            target_area: targetArea,
        });
    }

    function scheduleTextPadSave() {
        if (!dom.textpadEditor) {
            return;
        }

        if (state.textPadSaveTimer) {
            clearTimeout(state.textPadSaveTimer);
        }

        state.textPadSaveTimer = setTimeout(() => {
            saveTextPad().catch((error) => {
                console.error(error);
                if (dom.textpadStatus) {
                    dom.textpadStatus.textContent = 'Save failed';
                }
            });
        }, 650);
    }

    function scheduleCodeSave() {
        if (!codeEditorView) {
            return;
        }

        if (state.codeSaveTimer) {
            clearTimeout(state.codeSaveTimer);
        }

        syncActiveCodeFile();

        state.codeSaveTimer = setTimeout(() => {
            saveCode({ persist: false }).catch((error) => {
                console.error(error);
                if (dom.codeStatus) {
                    dom.codeStatus.textContent = 'Save failed';
                }
            });
        }, 650);
    }

    function maybeSendPointer(event) {
        sendPointerPoint(getPoint(dom.canvas, event), 'whiteboard');
    }

    function beginStroke(event) {
        if (!canEditWhiteboard(state)) {
            return;
        }

        if (state.currentTool === 'text' || state.currentTool === 'select') {
            return;
        }

        if (!['pen', 'eraser', 'shape_rect', 'shape_circle'].includes(state.currentTool)) {
            return;
        }

        state.isDrawing = true;
        const point = getPoint(dom.canvas, event);

        if (state.currentTool === 'pen' || state.currentTool === 'eraser') {
            state.activeStroke = {
                element_type: state.currentTool,
                data: {
                    tool: state.currentTool,
                    color: state.currentColor,
                    width: state.currentTool === 'eraser' ? 18 : 4,
                    points: [point],
                },
            };
        } else {
            state.activeStroke = {
                element_type: state.currentTool,
                data: {
                    start: point,
                    end: point,
                    color: state.currentColor,
                    lineWidth: 3,
                },
            };
        }

        renderWhiteboard(dom, state, state.activeStroke);
    }

    function extendStroke(event) {
        if (!state.isDrawing || !state.activeStroke) {
            maybeSendPointer(event);
            return;
        }

        const point = getPoint(dom.canvas, event);
        const { element_type: type, data } = state.activeStroke;

        if (type === 'pen' || type === 'eraser') {
            data.points.push(point);
        } else if (type === 'shape_rect' || type === 'shape_circle') {
            data.end = point;
        }

        renderWhiteboard(dom, state, state.activeStroke);
        maybeSendPointer(event);
    }

    function finishStroke(event) {
        if (!state.isDrawing || !state.activeStroke) {
            return;
        }

        const point = getPoint(dom.canvas, event);
        const { element_type: type, data } = state.activeStroke;

        if (type === 'pen' || type === 'eraser') {
            if (data.points.length === 1) {
                data.points.push(point);
            }
        } else if (type === 'shape_rect' || type === 'shape_circle') {
            data.end = point;
        }

        state.isDrawing = false;

        const finishedElement = state.activeStroke;
        state.activeStroke = null;
        renderWhiteboard(dom, state);

        submitWhiteboardElement(finishedElement).catch((error) => {
            console.error(error);
            appendSystemLine(dom, 'Whiteboard update failed to save.');
        });
    }

    function handleTextPlacement(event) {
        if (state.currentTool !== 'text' || !canEditWhiteboard(state)) {
            return;
        }

        const text = clampText(window.prompt('Add shared text'));
        if (!text) {
            return;
        }

        const point = getPoint(dom.canvas, event);
        submitWhiteboardElement({
            element_type: 'text',
            data: {
                x: point.x,
                y: point.y,
                text,
                color: state.currentColor,
                fontSize: 18,
            },
        }).catch((error) => {
            console.error(error);
            appendSystemLine(dom, 'Text note could not be saved.');
        });
    }

    function bindRealtimeHandlers() {
        if (!window.Echo) {
            return;
        }

        state.hasEcho = connectEcho(dom, state, {
            onWhiteboardElementCreated: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                applyWhiteboardElement({
                    id: payload.id,
                    user_id: payload.user_id,
                    user_name: payload.user_name,
                    element_type: payload.element_type,
                    data: payload.data,
                    created_at: payload.created_at,
                });
            },
            onWhiteboardElementUpdated: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                applyWhiteboardElement({
                    id: payload.id,
                    user_id: payload.user_id,
                    user_name: payload.user_name,
                    element_type: payload.element_type,
                    data: payload.data,
                    created_at: payload.created_at,
                    updated_at: payload.updated_at,
                }, { fromServer: true });
            },
            onWhiteboardCleared: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                const pageKey = payload.page_key || null;
                if (pageKey) {
                    state.whiteboardElements = state.whiteboardElements.filter((element) => String(element.data?.page_key) !== String(pageKey));
                } else {
                    state.whiteboardElements = [];
                }
                whiteboardBoard?.clearRemote?.(pageKey).catch((error) => console.error(error));
                appendSystemLine(dom, pageKey
                    ? `${payload.user_name || 'Someone'} cleared page ${pageKey}.`
                    : `${payload.user_name || 'Someone'} cleared the shared whiteboard.`);
            },
            onWhiteboardElementDeleted: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                whiteboardBoard?.removeRemoteElement?.(payload.id).catch((error) => console.error(error));
                state.whiteboardElements = state.whiteboardElements.filter((element) => String(element.id) !== String(payload.id));
                appendSystemLine(dom, `${payload.user_name || 'Someone'} removed a whiteboard object.`);
            },
            onWhiteboardPageCreated: (payload) => {
                state.whiteboardState = payload.whiteboard_state || state.whiteboardState || defaultWhiteboardState();
                whiteboardBoard?.setWhiteboardState?.(state.whiteboardState);
                appendSystemLine(dom, `${payload.user_name || 'Someone'} created ${payload.title || 'a page'}.`);
            },
            onWhiteboardPageChanged: (payload) => {
                state.whiteboardState = {
                    ...(state.whiteboardState || defaultWhiteboardState()),
                    ...payload.whiteboard_state,
                    active_page: payload.page_key || payload.whiteboard_state?.active_page || state.whiteboardState?.active_page || 'page-1',
                };
                whiteboardBoard?.setWhiteboardState?.(state.whiteboardState);
                appendSystemLine(dom, `${payload.user_name || 'Someone'} switched the board page.`);
            },
            onWhiteboardPageDeleted: (payload) => {
                const removedKey = String(payload.page_key || '');
                state.whiteboardState = {
                    ...(state.whiteboardState || defaultWhiteboardState()),
                    pages: (state.whiteboardState?.pages || []).filter((page) => String(page.key) !== removedKey),
                };
                whiteboardBoard?.setWhiteboardState?.(state.whiteboardState);
                appendSystemLine(dom, `${payload.user_name || 'Someone'} deleted ${payload.title || 'a page'}.`);
                loadWhiteboardSnapshots().catch((error) => console.error(error));
            },
            onWhiteboardBackgroundChanged: (payload) => {
                const pages = (state.whiteboardState?.pages || []).map((page) => (String(page.key) === String(payload.page_key)
                    ? {
                        ...page,
                        background_type: payload.background_type || page.background_type,
                        background_value: payload.background_value || page.background_value,
                    }
                    : page));
                state.whiteboardState = {
                    ...(state.whiteboardState || defaultWhiteboardState()),
                    pages,
                };
                whiteboardBoard?.setWhiteboardState?.(state.whiteboardState);
                appendSystemLine(dom, `${payload.user_name || 'Someone'} changed the board background.`);
            },
            onWhiteboardElementsMoved: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                appendSystemLine(dom, `${payload.user_name || 'Someone'} moved board objects.`);
                loadWhiteboard().catch((error) => console.error(error));
            },
            onWhiteboardSnapshotCreated: (payload) => {
                appendSystemLine(dom, `${payload.user_name || 'Someone'} created snapshot ${payload.name || ''}`.trim());
                loadWhiteboardSnapshots().catch((error) => console.error(error));
            },
            onWhiteboardPermissionChanged: (payload) => {
                if (Number(payload.user_id) !== Number(state.userId)) {
                    appendSystemLine(dom, `${payload.user_name || 'Someone'} updated whiteboard permissions.`);
                }
                loadSessionState().catch((error) => console.error(error));
            },
            onChatMessage: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                renderMessage(dom, state, {
                    id: payload.id,
                    user_id: payload.user_id,
                    user_name: payload.user_name,
                    message: payload.message,
                    created_at: payload.created_at,
                });
            },
            onCodeUpdated: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                setCodeDraft(payload.code || '', {
                    source: payload.user_name || 'someone else',
                    status: payload.user_name ? `Updated by ${payload.user_name}` : 'Updated',
                    files: payload.files || null,
                    activeFileKey: payload.active_file_key || state.activeCodeFileKey,
                });
            },
            onCodeSaved: (payload) => {
                setCodeDraft(payload.code || state.currentCode, {
                    source: payload.user_name || 'someone else',
                    status: payload.saved_at
                        ? `Saved ${formatClock(payload.saved_at)}`
                        : `Saved by ${payload.user_name || 'someone else'}`,
                    files: payload.files || null,
                    activeFileKey: payload.active_file_key || state.activeCodeFileKey,
                });

                if (Number(payload.user_id) !== Number(state.userId)) {
                    appendSystemLine(dom, `${payload.user_name || 'Someone'} saved the shared code.`);
                }
            },
            onPointerMoved: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                upsertPointer(dom, state, {
                    user_id: payload.user_id,
                    x_position: payload.x_position,
                    y_position: payload.y_position,
                    user_name: payload.user_name,
                    target_area: payload.target_area,
                });
            },
            onParticipantJoined: (payload) => {
                if (Number(payload.user_id) !== Number(state.userId)) {
                    appendSystemLine(dom, `${payload.user_name} joined the room as ${payload.role}.`);
                    loadParticipants().catch((error) => console.error(error));
                }
            },
            onParticipantLeft: (payload) => {
                if (Number(payload.user_id) !== Number(state.userId)) {
                    appendSystemLine(dom, `${payload.user_name} left the room.`);
                    loadParticipants().catch((error) => console.error(error));
                }
            },
            onStudentPermissionChanged: (payload) => {
                applyRoomPermissions(payload.permissions || {});

                if (Number(payload.user_id) !== Number(state.userId)) {
                    appendSystemLine(dom, `${payload.user_name || 'Someone'} updated student permissions.`);
                }
            },
            onModeChanged: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                updateModeUi(dom, state, payload.mode || state.currentMode);
                appendSystemLine(dom, `Mode changed to ${MODE_LABELS[payload.mode] || payload.mode}.`);
            },
            onStatusChanged: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                if (dom.activityStatus) {
                    dom.activityStatus.textContent = payload.status === 'ended' ? 'Session ended' : 'Live session';
                }

                if (dom.liveSyncStatus) {
                    dom.liveSyncStatus.textContent = payload.status === 'ended' ? 'Session ended' : 'Live session';
                }

                if (dom.sessionConnectionStatus) {
                    dom.sessionConnectionStatus.textContent = payload.status === 'ended' ? 'Session ended' : 'Live session';
                }

                appendSystemLine(dom, payload.status === 'ended'
                    ? `${payload.user_name || 'Teacher'} ended the session.`
                    : `${payload.user_name || 'Teacher'} opened the live session.`);

                if (payload.status === 'ended') {
                    window.setTimeout(() => window.location.reload(), 1200);
                }
            },
            onTextPadUpdated: (payload) => {
                if (Number(payload.user_id) === Number(state.userId)) {
                    return;
                }

                state.textPadSnapshot = sanitizeTextPadHtml(payload.content || '');
                state.textPadComments = normalizeTextPadComments(payload.comments || state.textPadComments);
                setTextPadHtml(dom, state.textPadSnapshot);
                updateTextPadWordCount(dom, state.textPadSnapshot);
                renderTextPadComments(dom, state);

                if (dom.textpadStatus) {
                    dom.textpadStatus.textContent = `Updated by ${payload.user_name || 'someone else'}`;
                }

                updateTextPadTypingStatus(dom, `${payload.user_name || 'Someone'} updated the shared writing pad.`);

                appendSystemLine(dom, `${payload.user_name || 'Someone'} updated the shared pad.`);
            },
        });

        if (state.hasEcho) {
            whiteboardBoard?.setConnectionState?.('Live sync connected');
        }
    }

    function bindControls() {
        updateToolButtons(state.currentTool);
        updateColorButtons(state.currentColor);
        updateModeUi(dom, state, state.currentMode);
        renderRoomPermissions();
        syncInteractiveControls();
        renderCodeWorkspace();
        toggleTab(dom, 'chat');

        dom.modeButtons.forEach((button) => {
            button.addEventListener('click', async (event) => {
                const nextMode = button.dataset.mode || 'whiteboard';

                if (!state.isTeacher) {
                    return;
                }

                event.preventDefault();

                try {
                    await changeMode(nextMode);
                } catch (error) {
                    console.error(error);
                    appendSystemLine(dom, 'Mode change failed to save.');
                }
            });
        });

        dom.tabJumpButtons.forEach((button) => {
            button.addEventListener('click', () => {
                toggleTab(dom, button.dataset.classroomTabJump || 'chat');
            });
        });

        dom.quickRoomActionButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.roomAction;
                if (!state.isTeacher) {
                    return;
                }

                if (action === 'share-screen') {
                    copyTextToClipboard(state.joinLink || state.roomCode || '').then(() => {
                        appendSystemLine(dom, 'Room link copied.');
                    });
                    return;
                }

                if (action === 'mute-all') {
                    const permissions = {
                        ...state.roomPermissions,
                        chat: false,
                    };
                    state.roomPermissions = permissions;
                    renderRoomPermissions(permissions);
                    applyPermissions().catch((error) => console.error(error));
                    return;
                }

                if (action === 'lock-room') {
                    const permissions = {
                        ...state.roomPermissions,
                        draw: false,
                        type: false,
                        chat: false,
                        code: false,
                        pointer: false,
                        download: false,
                        whiteboard_draw: false,
                        whiteboard_text: false,
                        whiteboard_shapes: false,
                        whiteboard_images: false,
                        whiteboard_erase: false,
                        whiteboard_pointer: false,
                        whiteboard_comments: false,
                        whiteboard_page_switch: false,
                        whiteboard_page_create: false,
                        whiteboard_object_move: false,
                        whiteboard_download: false,
                        whiteboard_follow_teacher_page: false,
                        whiteboard_follow_teacher_viewport: false,
                    };
                    state.roomPermissions = permissions;
                    renderRoomPermissions(permissions);
                    applyPermissions().catch((error) => console.error(error));
                }
            });
        });

        if (dom.codeFileTabs) {
            dom.codeFileTabs.addEventListener('click', (event) => {
                const button = event.target.closest('[data-code-file-tab]');
                if (!button || !canUseCodeEditor(state)) {
                    return;
                }

                switchCodeFile(button.dataset.codeFileTab || 'html');
            });
        }

        if (dom.codeAddFileBtn) {
            dom.codeAddFileBtn.addEventListener('click', () => {
                if (!canUseCodeEditor(state)) {
                    return;
                }

                createCodeFile();
            });
        }

        function closeCodeMoreMenu() {
            if (dom.codeMoreMenu) {
                dom.codeMoreMenu.classList.add('hidden');
            }
            if (dom.codeMoreBtn) {
                dom.codeMoreBtn.setAttribute('aria-expanded', 'false');
            }
        }

        if (dom.codeMoreBtn && dom.codeMoreMenu) {
            dom.codeMoreBtn.setAttribute('aria-expanded', 'false');
            dom.codeMoreBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                const shouldOpen = dom.codeMoreMenu.classList.contains('hidden');
                dom.codeMoreMenu.classList.toggle('hidden', !shouldOpen);
                dom.codeMoreBtn.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            });

            document.addEventListener('click', (event) => {
                if (!dom.codeMoreMenu || dom.codeMoreMenu.classList.contains('hidden')) {
                    return;
                }

                if (dom.codeMoreMenu.contains(event.target) || dom.codeMoreBtn.contains(event.target)) {
                    return;
                }

                closeCodeMoreMenu();
            });
        }

        if (dom.codeRenameFileBtn) {
            dom.codeRenameFileBtn.addEventListener('click', () => {
                if (!canUseCodeEditor(state)) {
                    return;
                }

                renameActiveCodeFile();
                dom.codeMoreMenu?.classList.add('hidden');
            });
        }

        if (dom.codeDeleteFileBtn) {
            dom.codeDeleteFileBtn.addEventListener('click', () => {
                if (!canUseCodeEditor(state)) {
                    return;
                }

                deleteActiveCodeFile();
                dom.codeMoreMenu?.classList.add('hidden');
            });
        }

        if (dom.runPreviewBtn) {
            dom.runPreviewBtn.addEventListener('click', () => {
                runCodePreview();
            });
        }

        if (dom.codePreviewRefreshBtn) {
            dom.codePreviewRefreshBtn.addEventListener('click', () => {
                if (!canUseCodeEditor(state)) {
                    return;
                }

                runCodePreview();
            });
        }

        if (dom.resetCodeBtn) {
            dom.resetCodeBtn.addEventListener('click', () => {
                if (!canUseCodeEditor(state)) {
                    return;
                }

                if (!window.confirm('Reset the code files back to the lesson starter?')) {
                    return;
                }

                resetCodeWorkspace();
                dom.codeMoreMenu?.classList.add('hidden');
            });
        }

        dom.tabButtons.forEach((button) => {
            button.addEventListener('click', () => toggleTab(dom, button.dataset.classroomTab || 'chat'));
        });

        if (dom.textpadEditor) {
            dom.textpadEditor.addEventListener('input', () => {
                if (!canUseTextPad(state)) {
                    return;
                }

                state.textPadSnapshot = getTextPadHtml(dom);
                dom.textpadEditor.dataset.empty = isTextPadBlankHtml(state.textPadSnapshot) ? 'true' : 'false';
                if (dom.textpadStatus) {
                    dom.textpadStatus.textContent = 'Typing...';
                }

                updateTextPadWordCount(dom, state.textPadSnapshot);
                updateTextPadTypingStatus(dom, state.isTeacher ? 'Teacher is editing the shared pad.' : 'Learner is typing in the shared pad.');
                scheduleTextPadSave();
            });

            dom.textpadEditor.addEventListener('focus', () => updateTextPadSelectionStatus(dom));
            dom.textpadEditor.addEventListener('mouseup', () => updateTextPadSelectionStatus(dom));
            dom.textpadEditor.addEventListener('keyup', () => updateTextPadSelectionStatus(dom));
        }

        document.addEventListener('selectionchange', () => {
            updateTextPadSelectionStatus(dom);
        });

        if (dom.textpadToolbarButtons.length) {
            dom.textpadToolbarButtons.forEach((button) => {
                button.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                });
                button.addEventListener('click', () => {
                    if (!canUseTextPad(state)) {
                        return;
                    }

                    const command = button.dataset.textpadCommand;
                    const block = button.dataset.textpadBlock;
                    const action = button.dataset.textpadAction;

                    if (action === 'clear-formatting') {
                        execTextPadCommand(dom, state, 'removeFormat');
                        execTextPadCommand(dom, state, 'unlink');
                    } else if (block) {
                        execTextPadCommand(dom, state, 'formatBlock', block === 'blockquote' ? 'blockquote' : block.toUpperCase());
                    } else if (command) {
                        execTextPadCommand(dom, state, command);
                    }

                    state.textPadSnapshot = getTextPadHtml(dom);
                    updateTextPadWordCount(dom, state.textPadSnapshot);
                    dom.textpadEditor.dataset.empty = isTextPadBlankHtml(state.textPadSnapshot) ? 'true' : 'false';
                    updateTextPadSelectionStatus(dom);
                    if (dom.textpadStatus) {
                        dom.textpadStatus.textContent = 'Formatting...';
                    }
                    scheduleTextPadSave();
                    focusTextPadEditor(dom);
                });
            });
        }

        if (dom.textpadCommentAddBtn) {
            dom.textpadCommentAddBtn.addEventListener('click', () => {
                if (!state.isTeacher || !dom.textpadCommentInput) {
                    return;
                }

                const message = clampText(dom.textpadCommentInput.value);
                if (!message) {
                    dom.textpadCommentInput.focus();
                    return;
                }

                appendTextPadComment(state, dom, message);
                dom.textpadCommentInput.value = '';
                if (dom.textpadStatus) {
                    dom.textpadStatus.textContent = 'Comment added';
                }

                saveTextPad().catch((error) => {
                    console.error(error);
                    if (dom.textpadStatus) {
                        dom.textpadStatus.textContent = 'Save failed';
                    }
                });
            });
        }

        if (dom.sessionNotesEditor) {
            dom.sessionNotesEditor.addEventListener('input', () => {
                state.sessionNotes = dom.sessionNotesEditor.value;
            });
        }

        if (dom.copyButtons.length) {
            dom.copyButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const text = button.dataset.copyText || '';
                    copyTextToClipboard(text)
                        .then(() => appendSystemLine(dom, 'Copied to clipboard.'))
                        .catch((error) => {
                            console.error(error);
                            appendSystemLine(dom, 'Copy failed.');
                        });
                });
            });
        }

        if (dom.saveSessionButtons.length) {
            dom.saveSessionButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (!state.isTeacher) {
                        return;
                    }

                    saveSessionSnapshot().catch((error) => {
                        console.error(error);
                        appendSystemLine(dom, 'Session snapshot could not be saved.');
                    });
                });
            });
        }

        if (dom.codeSaveBtn) {
            dom.codeSaveBtn.addEventListener('click', () => {
                saveCode({ persist: true }).catch((error) => {
                    console.error(error);
                    if (dom.codeStatus) {
                        dom.codeStatus.textContent = 'Save failed';
                    }
                });
            });
        }

        if (dom.codeConsoleClearBtn) {
            dom.codeConsoleClearBtn.addEventListener('click', () => {
                clearCodeConsole('Preview output appears here.');
            });
        }

        if (dom.applyPermissionsBtn) {
            dom.applyPermissionsBtn.addEventListener('click', () => {
                applyPermissions().catch((error) => {
                    console.error(error);
                    appendSystemLine(dom, 'Student permissions could not be updated.');
                });
            });
        }

        if (dom.chatInput) {
            dom.chatInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    if (dom.chatSendBtn) {
                        dom.chatSendBtn.click();
                    }
                }
            });
        }

        if (dom.chatSendBtn) {
            dom.chatSendBtn.addEventListener('click', () => {
                sendChatMessage().catch((error) => {
                    console.error(error);
                    appendSystemLine(dom, 'Chat message could not be sent.');
                });
            });
        }

        if (dom.textpadSaveBtn) {
            dom.textpadSaveBtn.addEventListener('click', () => {
                saveTextPad().catch((error) => {
                    console.error(error);
                    if (dom.textpadStatus) {
                        dom.textpadStatus.textContent = 'Save failed';
                    }
                });
            });
        }

        window.addEventListener('message', (event) => {
            if (event.data?.source !== 'classbridge-preview') {
                return;
            }

            if (!dom.codeOutput) {
                return;
            }

            if (event.data.type === 'ready') {
                if (state.previewAwaitingReady) {
                    appendConsoleLine('Preview updated successfully.', 'info');
                    if (!state.previewHasError) {
                        appendConsoleLine('No errors found.', 'info');
                    }
                    state.previewAwaitingReady = false;
                } else {
                    appendConsoleLine(event.data.payload, 'info');
                }

                if (dom.codeStatus) {
                    dom.codeStatus.textContent = state.previewHasError ? 'Preview error' : 'Preview ready';
                }

                if (dom.codeStatusInline) {
                    dom.codeStatusInline.textContent = state.previewHasError ? 'Preview error' : 'Preview ready';
                }
                return;
            }

            appendConsoleLine(event.data.payload, event.data.type === 'error' ? 'error' : (event.data.type === 'warn' ? 'warn' : 'info'));
        });
    }

    function bindFallbackSync() {
        if (state.hasEcho) {
            return;
        }

        whiteboardBoard?.setConnectionState?.('Polling sync');

        window.setInterval(() => {
            loadWhiteboard().catch((error) => console.error(error));
            loadParticipants().catch((error) => console.error(error));
            loadPointers().catch((error) => console.error(error));
            loadSessionState().catch((error) => console.error(error));
            loadTextPad().catch((error) => console.error(error));
            loadCode().catch((error) => console.error(error));
        }, 8000);

        window.setInterval(() => {
            loadMessages({ sinceId: state.lastMessageId }).catch((error) => console.error(error));
        }, 5000);
    }

    async function bootstrap() {
        resizeCanvas();
        whiteboardBoard = createWhiteboardFoundation({
            root: dom.whiteboardRoot,
            canvas: dom.canvas,
            canvasContainer: dom.canvasContainer,
            pointerLayer: dom.pointerLayer,
            sessionId: state.sessionId,
            userId: state.userId,
            isTeacher: state.isTeacher,
            whiteboardState: state.whiteboardState,
            canEdit: () => canEditWhiteboard(state),
            canUsePointer: () => canUsePointer(state),
            saveElement: submitWhiteboardElement,
            deleteElement: deleteWhiteboardElement,
            clearBoard: clearWhiteboard,
            createSnapshot: createWhiteboardSnapshot,
            restoreSnapshot: restoreWhiteboardSnapshot,
            saveLayout: async (whiteboardState) => {
                state.whiteboardState = whiteboardState;
                await saveSessionSnapshot({ whiteboardState });
            },
            rightPanelOpen: false,
            sendPointer: (point, targetArea = 'whiteboard') => sendPointerPoint(point, targetArea),
            onActivity: (message) => appendSystemLine(dom, message),
        });

        whiteboardBoard?.resize?.();
        bindControls();
        initCodeEditor();
        toggleTab(dom, 'chat');

        await Promise.all([
            loadSessionState(),
            loadWhiteboard(),
            loadWhiteboardSnapshots(),
            loadMessages({ replace: true }),
            loadParticipants(),
            loadPointers(),
            loadTextPad(),
            loadCode(),
        ]);

        appendSystemLine(dom, 'Live teaching workspace connected.');
        bindRealtimeHandlers();
        bindFallbackSync();

        window.addEventListener('resize', resizeCanvas);

        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon(`/api/classroom/${state.sessionId}/leave`, new FormData());
        });
    }

    bootstrap().catch((error) => {
        console.error(error);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startClassroomWorkspace);
} else {
    startClassroomWorkspace();
}

