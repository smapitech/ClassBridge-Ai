@extends('layouts.dashboard')
@section('title', 'Join Coding Session')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <section class="cb-surface px-6 py-8 sm:px-8">
        <x-status-badge tone="info">Live Coding Studio</x-status-badge>
        <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Join a live coding session</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
            Enter the session code from your teacher or tutor to open the same protected coding workspace. Teacher and student edit, preview, chat, and review together inside ClassBridge AI.
        </p>

        <div class="mt-6 rounded-[1.75rem] border border-slate-200 bg-slate-50 px-5 py-4 text-sm leading-6 text-slate-600">
            <span class="font-semibold text-slate-900">Safety notice:</span>
            Teachers cannot access the student&apos;s computer, files, desktop, browser history, or other applications. All teaching happens inside the protected coding workspace.
        </div>
    </section>

    <section class="cb-surface p-6 sm:p-8">
        <form method="POST" action="{{ route('coding.sessions.join') }}" class="space-y-5">
            @csrf
            <div>
                <label for="join_code" class="block text-sm font-semibold text-slate-700">Session code</label>
                <input
                    id="join_code"
                    name="join_code"
                    value="{{ old('join_code', $prefillJoinCode) }}"
                    placeholder="CB-1234-5678"
                    class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('join_code') border-rose-500 @enderror"
                    required
                >
                @error('join_code')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <x-primary-button type="submit">
                    Join session
                </x-primary-button>
                <x-secondary-button href="{{ route('coding.assignments.index') }}">
                    Open coding assignments
                </x-secondary-button>
            </div>
        </form>
    </section>
</div>
@endsection
