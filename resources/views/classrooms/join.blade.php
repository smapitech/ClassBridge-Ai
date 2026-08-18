@extends('layouts.dashboard')
@section('title', 'Join Classroom')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <x-page-header
        eyebrow="Live Interactive Classroom"
        title="Enter a classroom room code."
        description="Join the secure workspace for a live lesson, tutoring session, homeschool class, or coding support room."
        badge="Protected"
        badgeTone="info"
    />

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <form method="POST" action="{{ route('classrooms.join-by-code') }}" class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm sm:p-8">
            @csrf
            <div>
                <label class="text-sm font-semibold text-slate-700">Room code</label>
                <input
                    name="room_code"
                    value="{{ old('room_code', $prefillRoomCode ?? '') }}"
                    required
                    placeholder="CLASS-ABCD-1234"
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-center font-mono text-lg tracking-[0.2em] outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                >
                @error('room_code')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <x-primary-button type="submit" class="mt-6 w-full justify-center">
                Join classroom
            </x-primary-button>

            @if (session('error'))
                <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('error') }}
                </div>
            @endif
        </form>

        <aside class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">What happens next</p>
            <div class="mt-5 space-y-4 text-sm leading-6 text-slate-600">
                <p>The code opens the protected learning workspace only if the room is active.</p>
                <p>Once inside, the teacher and learner can use the same board, shared pad, and live pointers together.</p>
                <p>Nothing on the learner's device is shared outside the ClassBridge AI classroom.</p>
            </div>
        </aside>
    </section>
</div>
@endsection
