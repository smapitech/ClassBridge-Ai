@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="mx-auto grid min-h-[calc(100vh-80px)] max-w-7xl gap-8 px-6 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:py-16">
    <div class="flex items-center">
        <div class="max-w-xl">
            <x-status-badge tone="info">
                Protected workspace access
            </x-status-badge>
            <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">
                Sign in to continue teaching, learning, and reviewing inside ClassBridge AI.
            </h1>
            <p class="mt-5 text-lg leading-8 text-slate-600">
                Whether you are an organization owner, tutor, learner, or parent, your account opens the same secure classroom workspace.
            </p>

            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">Live teaching</p>
                    <p class="mt-1 text-sm text-slate-600">Shared whiteboard, pointers, and text pad.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm">
                    <p class="text-sm font-semibold text-slate-900">Safe by design</p>
                    <p class="mt-1 text-sm text-slate-600">No access to private device files or apps.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-center">
        <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white/95 p-8 shadow-2xl shadow-slate-950/10">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black tracking-[0.2em] text-white shadow-lg shadow-slate-950/20">CB</div>
                <h2 class="mt-4 text-2xl font-black text-slate-900">Welcome back</h2>
                <p class="mt-2 text-sm text-slate-500">Sign in to your ClassBridge AI workspace.</p>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                        class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder-slate-400 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder-slate-400 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('password') border-rose-500 @enderror">
                    @error('password')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-950">
                    Remember me
                </label>

                <x-primary-button type="submit" class="w-full justify-center">
                    Sign in
                </x-primary-button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                New to ClassBridge AI?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Create your workspace</a>
            </p>
        </div>
    </div>
</div>
@endsection
