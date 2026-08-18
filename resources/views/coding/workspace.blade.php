@extends('layouts.dashboard')
@section('title', $assignment->title)

@push('head')
<meta name="project-id" content="{{ $project->id }}">
<meta name="submission-id" content="{{ $submission->id }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="flex flex-col h-[calc(100vh-130px)]">

    <!-- TOP BAR -->
    <div class="flex items-center justify-between bg-white border-b border-gray-200 px-4 py-2 flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-lg font-bold text-gray-900">{{ $assignment->title }}</h1>
            <span class="px-2 py-0.5 text-xs rounded-full
                {{ $submission->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' : ($submission->status === 'reviewed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600') }}">
                {{ ucfirst($submission->status) }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 mr-2" id="save-status"></span>
            <button onclick="runPreview()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Run Preview</button>
            <button onclick="saveProject()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Save</button>
            @if($submission->status !== 'submitted' && $submission->status !== 'reviewed')
                <form method="POST" action="{{ route('coding.submit', $submission) }}" onsubmit="return confirm('Submit this assignment? You cannot edit after submitting.')">@csrf
                    <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Submit</button>
                </form>
            @endif
            <button onclick="resetToStarter()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Reset</button>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden">

        <!-- LEFT: Editor -->
        <div class="flex-1 flex flex-col overflow-hidden border-r border-gray-200">
            <!-- Tab Buttons -->
            <div class="flex bg-gray-100 border-b border-gray-200 text-sm flex-shrink-0" id="editor-tabs">
                <button class="editor-tab px-4 py-2 font-medium border-b-2 border-indigo-600 text-indigo-600 bg-white" data-tab="html">HTML</button>
                <button class="editor-tab px-4 py-2 font-medium text-gray-500 hover:text-gray-700" data-tab="css">CSS</button>
                <button class="editor-tab px-4 py-2 font-medium text-gray-500 hover:text-gray-700" data-tab="js">JavaScript</button>
            </div>
            <!-- Editor Panels -->
            <div class="flex-1 overflow-hidden">
                <textarea id="editor-html" class="editor-pane w-full h-full p-4 font-mono text-sm border-0 resize-none focus:ring-0" spellcheck="false" placeholder="Write HTML here...">{{ $project->files->where('filename','index.html')->first()?->content ?? '' }}</textarea>
                <textarea id="editor-css" class="editor-pane w-full h-full p-4 font-mono text-sm border-0 resize-none focus:ring-0 hidden" spellcheck="false" placeholder="Write CSS here...">{{ $project->files->where('filename','style.css')->first()?->content ?? '' }}</textarea>
                <textarea id="editor-js" class="editor-pane w-full h-full p-4 font-mono text-sm border-0 resize-none focus:ring-0 hidden" spellcheck="false" placeholder="Write JavaScript here...">{{ $project->files->where('filename','script.js')->first()?->content ?? '' }}</textarea>
            </div>
        </div>

        <!-- RIGHT: Preview + Instructions -->
        <div class="w-[45%] flex flex-col overflow-hidden">
            <!-- Preview -->
            <div class="flex-1 flex flex-col">
                <div class="bg-gray-100 border-b border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 flex-shrink-0">Live Preview</div>
                <iframe id="preview-frame" class="flex-1 w-full bg-white border-0" sandbox="allow-scripts" title="Code Preview"></iframe>
            </div>
            <!-- Instructions / Feedback -->
            <div class="h-40 overflow-y-auto border-t border-gray-200 bg-gray-50 p-4 text-sm flex-shrink-0">
                <h3 class="font-semibold text-gray-900 mb-2">📋 Instructions</h3>
                <div class="text-gray-600 whitespace-pre-line">{{ $assignment->instructions ?? $assignment->description ?? 'No instructions provided.' }}</div>
                @if($submission->teacher_feedback)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-1">👨‍🏫 Teacher Feedback</h3>
                        <p class="text-gray-600">{{ $submission->teacher_feedback }}</p>
                        @if($submission->score !== null)<p class="text-sm font-semibold text-indigo-600 mt-1">Score: {{ $submission->score }}/100</p>@endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
const projectId = document.querySelector('meta[name="project-id"]')?.content;
const submissionId = document.querySelector('meta[name="submission-id"]')?.content;
let autoPreview = true;

// Tab switching
document.querySelectorAll('.editor-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.editor-tab').forEach(b => { b.classList.remove('border-b-2','border-indigo-600','text-indigo-600','bg-white'); b.classList.add('text-gray-500'); });
        this.classList.add('border-b-2','border-indigo-600','text-indigo-600','bg-white');
        this.classList.remove('text-gray-500');
        document.querySelectorAll('.editor-pane').forEach(p => p.classList.add('hidden'));
        document.getElementById('editor-' + this.dataset.tab).classList.remove('hidden');
    });
});

// Auto-preview on typing (debounced)
let previewTimeout;
['editor-html','editor-css','editor-js'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => {
        if (autoPreview) { clearTimeout(previewTimeout); previewTimeout = setTimeout(runPreview, 800); }
    });
});

// runPreview: combine HTML + CSS + JS into iframe
function runPreview() {
    const html = document.getElementById('editor-html')?.value || '';
    const css = document.getElementById('editor-css')?.value || '';
    const js = document.getElementById('editor-js')?.value || '';

    const combined = `<!DOCTYPE html>
<html>
<head><style>${css}</style></head>
<body>
${html}
<script>${js}<\/script>
</body>
</html>`;

    const frame = document.getElementById('preview-frame');
    frame.srcdoc = combined;
}

// saveProject: AJAX save to DB
async function saveProject() {
    if (!projectId) return;
    const statusEl = document.getElementById('save-status');
    statusEl.textContent = 'Saving...';

    try {
        const res = await fetch(`/coding/projects/${projectId}/save`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                html: document.getElementById('editor-html')?.value || '',
                css: document.getElementById('editor-css')?.value || '',
                js: document.getElementById('editor-js')?.value || '',
            })
        });
        const data = await res.json();
        if (data.success) { statusEl.textContent = 'Saved ✓'; setTimeout(() => statusEl.textContent = '', 2000); }
    } catch(e) { statusEl.textContent = 'Error saving'; }
}

// Keyboard shortcut: Ctrl+S to save
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveProject(); }
});

// resetToStarter: reload starter code
function resetToStarter() {
    if (!confirm('Reset all code to starter template? Your current code will be lost.')) return;

    // Reload the page to get fresh starter code from server
    window.location.reload();
}

// Initial preview load
document.addEventListener('DOMContentLoaded', () => { runPreview(); });
</script>
@endsection