@extends('layouts.dashboard')
@section('title', 'Parent Portal')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-[2rem] border border-white/70 bg-white/95 p-8 shadow-2xl shadow-slate-950/10 sm:p-10">
        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Parent portal</p>
        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">No child profile is linked yet.</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
            Link your child to this parent account so you can view attendance, reports, feedback, and progress inside ClassBridge AI.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">What you can see</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Attendance, homework, quiz scores, teacher feedback, progress reports, and lesson replays.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">What to do next</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">Ask the teacher, school admin, or tutor to connect your child to this parent account.</p>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('parent.dashboard') }}" class="rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                Open parent dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
