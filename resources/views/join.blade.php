@extends('layouts.public')
@section('title', 'Join a Live Lesson')

@section('content')
<div class="mx-auto max-w-6xl px-5 py-12 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[1.05fr_.95fr]">
        <section class="rounded-[2rem] border border-white/70 bg-white/95 p-6 shadow-2xl shadow-slate-950/10 sm:p-8">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.3em] text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                ClassBridge AI
            </div>

            <h1 class="mt-5 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">Join a Live Lesson</h1>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                Enter one room code to open the protected teaching room your teacher shared with you.
            </p>

            @if (! empty($invitationLinkMode))
                <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    Invitation link opened. Your room code is already filled in.
                </div>
            @endif

            @if (session('success'))
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('join.store') }}" class="mt-7 space-y-5">
                @csrf

                <div>
                    <label for="room_code" class="block text-sm font-semibold text-slate-700">Room code</label>
                    <input
                        id="room_code"
                        name="room_code"
                        value="{{ old('room_code', $prefillRoomCode ?? '') }}"
                        placeholder="CLASS-ABCD-1234"
                        autocomplete="off"
                        class="mt-2 block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-center font-mono text-lg tracking-[0.2em] text-slate-900 shadow-sm outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('room_code') border-rose-400 @enderror"
                        required
                    >
                    @error('room_code')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('join_code')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-primary-button type="submit" class="w-full justify-center py-4 text-base">
                    Join lesson
                </x-primary-button>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600">
                    <span class="font-semibold text-slate-900">Safety notice:</span>
                    You will join a protected ClassBridge lesson room. Your teacher can guide you inside the lesson but cannot access your computer, files, browser history, or other apps.
                </div>

                @guest
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                        Already have an account? <a href="{{ route('login') }}" class="font-semibold text-slate-950 hover:underline">Log in first</a> and we will continue your lesson join.
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                        Signed in as <span class="font-semibold text-slate-950">{{ auth()->user()->displayName() }}</span>.
                    </div>
                @endguest
            </form>
        </section>

        <aside class="space-y-4 rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-lg shadow-slate-950/10 sm:p-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">What happens next</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">One room. One lesson. One protected workspace.</h2>
            </div>

            <div class="space-y-3 text-sm leading-6 text-slate-600">
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="font-semibold text-slate-900">Teacher and learner share the same room.</p>
                    <p class="mt-1">Whiteboard, code, text, chat, and pointers stay in sync inside the lesson.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="font-semibold text-slate-900">No remote desktop access.</p>
                    <p class="mt-1">The teacher can guide, correct, and explain, but never sees the learner’s private device.</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4">
                    <p class="font-semibold text-slate-900">The lesson stays open if mode changes.</p>
                    <p class="mt-1">Switch from whiteboard to coding, text, mathematics, or presentation without changing rooms.</p>
                </div>
            </div>

            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm leading-6 text-emerald-900">
                Real-time sync will connect teacher and student actions instantly using WebSocket broadcasting.
            </div>
        </aside>
    </div>
</div>
@endsection
