@extends('layouts.dashboard')
@section('title', 'Review: ' . ($submission->student?->displayName() ?? 'Student'))

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
@php
    $htmlFile = $submission->project?->files?->where('filename', 'index.html')->first();
    $cssFile = $submission->project?->files?->where('filename', 'style.css')->first();
    $jsFile = $submission->project?->files?->where('filename', 'script.js')->first();
@endphp

<div class="space-y-6">
    <section class="cb-surface px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <a href="{{ route('coding.review', $assignment) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Back to reviews</a>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $submission->student?->displayName() ?? 'Unknown Student' }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $submission->student?->email }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-status-badge tone="{{ $submission->status === 'submitted' ? 'warning' : ($submission->status === 'reviewed' ? 'success' : 'neutral') }}">
                    {{ ucfirst($submission->status) }}
                </x-status-badge>
                <x-primary-button href="{{ route('coding.workspace', ['assignment' => $assignment, 'student_id' => $submission->student_id]) }}">
                    Open live studio
                </x-primary-button>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="cb-surface overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200/70 px-5 py-4">
                <div>
                    <p class="cb-page-kicker">Student code</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Submission files</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">{{ optional($submission->submitted_at)->format('M d, Y h:i A') ?? 'Not yet' }}</span>
            </div>

            <div class="border-b border-slate-200/70 bg-slate-50 px-5 py-3 text-sm">
                <div class="flex flex-wrap gap-2">
                    <button class="review-tab cb-ide-tab cb-ide-tab-active" data-tab="html" onclick="switchTab('html', this)">HTML</button>
                    <button class="review-tab cb-ide-tab cb-ide-tab-inactive" data-tab="css" onclick="switchTab('css', this)">CSS</button>
                    <button class="review-tab cb-ide-tab cb-ide-tab-inactive" data-tab="js" onclick="switchTab('js', this)">JavaScript</button>
                </div>
            </div>

            <div class="grid min-h-[34rem] lg:grid-cols-[1fr]">
                <pre class="review-pane m-0 overflow-auto bg-slate-950 p-5 text-xs text-emerald-300" id="code-html">{{ $htmlFile?->content ?: '<!-- No HTML -->' }}</pre>
                <pre class="review-pane m-0 hidden overflow-auto bg-slate-950 p-5 text-xs text-emerald-300" id="code-css">{{ $cssFile?->content ?: '/* No CSS */' }}</pre>
                <pre class="review-pane m-0 hidden overflow-auto bg-slate-950 p-5 text-xs text-emerald-300" id="code-js">{{ $jsFile?->content ?: '// No JavaScript' }}</pre>
            </div>
        </div>

        <div class="space-y-6">
            <div class="cb-surface overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-200/70 px-5 py-4">
                    <div>
                        <p class="cb-page-kicker">Preview</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Live result</h2>
                    </div>
                    <button type="button" onclick="runStudentPreview()" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">Refresh preview</button>
                </div>
                <iframe id="preview-frame" class="h-[24rem] w-full border-0 bg-white" sandbox="allow-scripts" title="Code Preview"></iframe>
            </div>

            <div class="cb-surface p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="cb-page-kicker">Review</p>
                        <h2 class="mt-2 text-lg font-bold text-slate-900">Feedback and score</h2>
                    </div>
                    <x-status-badge tone="info">Teacher only</x-status-badge>
                </div>

                <form method="POST" action="{{ route('coding.submission.review', $submission) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Feedback</label>
                        <textarea name="teacher_feedback" rows="4" class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="Provide feedback to the student...">{{ $submission->teacher_feedback }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Score (0-100)</label>
                            <input type="number" name="score" value="{{ $submission->score }}" min="0" max="100" step="0.5" class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400" placeholder="e.g. 85">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                                <option value="reviewed" {{ $submission->status === 'reviewed' ? 'selected' : '' }}>Reviewed (Complete)</option>
                                <option value="returned" {{ $submission->status === 'returned' ? 'selected' : '' }}>Returned (Needs Work)</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="cb-btn-primary w-full">Save Review</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.review-tab').forEach(b => { b.classList.remove('cb-ide-tab-active'); b.classList.add('cb-ide-tab-inactive'); });
    btn.classList.add('cb-ide-tab-active');
    btn.classList.remove('cb-ide-tab-inactive');
    document.querySelectorAll('.review-pane').forEach(p => p.classList.add('hidden'));
    document.getElementById('code-' + tab).classList.remove('hidden');
}

function runStudentPreview() {
    const html = document.getElementById('code-html')?.textContent || '';
    const css = document.getElementById('code-css')?.textContent || '';
    const js = document.getElementById('code-js')?.textContent || '';
    const combined = '<!DOCTYPE html>\n<html>\n<head><style>' + css + '</style></head>\n<body>\n' + html + '\n<script>' + js.replace(/<\/script/g, '<\\/script') + '<\\/script>\n</body>\n</html>';
    document.getElementById('preview-frame').srcdoc = combined;
}

document.addEventListener('DOMContentLoaded', () => { runStudentPreview(); });
</script>
@endsection
