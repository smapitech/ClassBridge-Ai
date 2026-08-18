@extends('layouts.dashboard')
@section('title', 'AI Teaching Assistant')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">AI Teaching Assistant</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $usageThisMonth }} generations this month</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="mb-4 flex flex-wrap gap-1 border-b border-gray-200">
        @foreach(['curriculum' => 'Curriculum', 'lesson_plan' => 'Lesson Plan', 'examples' => 'Examples', 'quiz' => 'Quiz', 'homework' => 'Homework', 'progress_report' => 'Report', 'general' => 'General'] as $key => $label)
            <button onclick="switchTab('{{ $key }}')" data-tab="{{ $key }}" class="ai-tab px-4 py-2 text-sm font-medium border-b-2 {{ $loop->first ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Generate Form --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 mb-6">
        <form method="POST" action="{{ route('ai.teacher.generate') }}" id="generate-form">
            @csrf
            <input type="hidden" name="type" id="form-type" value="curriculum">
            <input type="hidden" name="title" id="form-title">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                <div><label class="block text-xs font-medium text-gray-700">Subject</label><input type="text" name="subject" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="e.g. Mathematics"></div>
                <div><label class="block text-xs font-medium text-gray-700">Age Group</label><input type="text" name="age_group" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="e.g. 8-10 years"></div>
                <div><label class="block text-xs font-medium text-gray-700">Level</label><input type="text" name="level" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="e.g. Beginner"></div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Describe what you need</label>
                <textarea name="prompt" rows="4" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="Generate a lesson plan for...">{{ old('prompt') }}</textarea>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" id="generate-btn">
                    Generate
                </button>
                <button type="submit" formaction="{{ route('ai.teacher.save') }}" formmethod="POST" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Save Result
                </button>
                <span id="loading-spinner" class="hidden text-sm text-gray-500">Generating...</span>
            </div>
        </form>
    </div>

    {{-- Result Display --}}
    @if(session('result'))
        <div class="rounded-lg border border-gray-200 bg-white p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-900">Result</h2>
                <div class="flex items-center gap-2">
                    @if(session('provider_name'))
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                            {{ session('provider_name') }} · {{ session('model') }}
                        </span>
                    @endif
                    <button onclick="copyResult()" class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">Copy</button>
                </div>
            </div>
            <div id="result-content" class="prose prose-sm max-w-none whitespace-pre-wrap text-sm text-gray-700">{!! nl2br(e(session('result'))) !!}</div>
        </div>
    @endif

    {{-- Recent History --}}
    @if($history->isNotEmpty())
        <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 font-semibold text-sm text-gray-900">Recent Generations</div>
            <div class="divide-y divide-gray-100">
                @foreach($history as $gen)
                    <div class="px-4 py-3 text-sm hover:bg-gray-50 flex items-center justify-between">
                        <div>
                            <span class="font-medium text-gray-900">{{ $gen->title ?? ucfirst($gen->type) }}</span>
                            <span class="text-gray-400 text-xs ml-2">{{ $gen->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $gen->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($gen->status) }}</span>
                            @if($gen->provider)<span class="text-xs text-gray-400">{{ $gen->provider->name }}</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
let currentTab = 'curriculum';
function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.ai-tab').forEach(b => { b.classList.remove('border-indigo-600','text-indigo-600'); b.classList.add('border-transparent','text-gray-500'); });
    document.querySelector(`[data-tab="${tab}"]`).classList.add('border-indigo-600','text-indigo-600');
    document.getElementById('form-type').value = tab;
    document.getElementById('form-title').value = tab.replace('_',' ').replace(/\b\w/g, c => c.toUpperCase());
}
document.getElementById('generate-form')?.addEventListener('submit', () => {
    document.getElementById('loading-spinner')?.classList.remove('hidden');
    document.getElementById('generate-btn')?.setAttribute('disabled', true);
});
function copyResult() {
    const text = document.getElementById('result-content')?.textContent || '';
    navigator.clipboard.writeText(text).then(() => alert('Copied!'));
}
</script>
@endsection