@extends('layouts.dashboard')
@section('title', 'Preview: ' . $assignment->title)

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="{{ route('coding.assignments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to assignments</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Preview: {{ $assignment->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">See how the assignment and starter code appear to students.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Assignment Details --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Assignment Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium"><span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $assignment->status === 'published' ? 'bg-green-100 text-green-700' : ($assignment->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">{{ ucfirst($assignment->status) }}</span></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Class</dt><dd class="font-medium">{{ $assignment->classe?->name ?? 'All classes' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Subject</dt><dd class="font-medium">{{ $assignment->subject?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Due</dt><dd class="font-medium">{{ $assignment->due_at?->format('M d, Y h:i A') ?? 'No due date' }}</dd></div>
            </dl>

            @if($assignment->instructions)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Instructions</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $assignment->instructions }}</p>
                </div>
            @endif
        </div>

        {{-- Live Preview of Starter Code --}}
        <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 text-sm font-semibold text-gray-700">Starter Code Preview</div>
            <iframe class="w-full h-64 border-0" sandbox="allow-scripts" srcdoc="{{ $assignment->starterHtmlPreview() }}"></iframe>
        </div>

    </div>

    {{-- Starter Code Tabs --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-white overflow-hidden">
        <div class="flex bg-gray-100 border-b border-gray-200 text-sm">
            @foreach(['html' => 'HTML', 'css' => 'CSS', 'js' => 'JavaScript'] as $key => $label)
                <button class="preview-tab px-4 py-2 font-medium {{ $loop->first ? 'border-b-2 border-indigo-600 text-indigo-600 bg-white' : 'text-gray-500' }}" data-tab="{{ $key }}" onclick="switchPreviewTab('{{ $key }}', this)">{{ $label }}</button>
            @endforeach
        </div>
        <pre class="preview-pane m-0 p-4 bg-gray-900 text-green-400 text-xs overflow-x-auto" id="preview-html" style="max-height:300px">{{ $assignment->starter_html ?: '<!-- No starter HTML -->' }}</pre>
        <pre class="preview-pane m-0 p-4 bg-gray-900 text-green-400 text-xs overflow-x-auto hidden" id="preview-css" style="max-height:300px">{{ $assignment->starter_css ?: '/* No starter CSS */' }}</pre>
        <pre class="preview-pane m-0 p-4 bg-gray-900 text-green-400 text-xs overflow-x-auto hidden" id="preview-js" style="max-height:300px">{{ $assignment->starter_js ?: '// No starter JavaScript' }}</pre>
    </div>
</div>

<script>
function switchPreviewTab(tab, btn) {
    document.querySelectorAll('.preview-tab').forEach(b => { b.classList.remove('border-b-2','border-indigo-600','text-indigo-600','bg-white'); b.classList.add('text-gray-500'); });
    btn.classList.add('border-b-2','border-indigo-600','text-indigo-600','bg-white');
    btn.classList.remove('text-gray-500');
    document.querySelectorAll('.preview-pane').forEach(p => p.classList.add('hidden'));
    document.getElementById('preview-' + tab).classList.remove('hidden');
}
</script>
@endsection