@extends('layouts.dashboard')
@section('title', 'Custom Domains')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Custom Domains</h1>
        <p class="mt-2 text-sm text-slate-500">Request a domain for your organization workspace.</p>

        <form method="POST" action="{{ route('school.domains.request') }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
            @csrf
            <input name="domain" placeholder="learn.example.com" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm">
            <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Request domain</button>
        </form>
    </div>

    <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Requested domains</h2>
        <div class="mt-5 space-y-3">
            @forelse ($domains as $domain)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-sm font-semibold text-slate-900">{{ $domain->domain }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ ucfirst($domain->status) }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No domain requests yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
