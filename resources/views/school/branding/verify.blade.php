@extends('layouts.dashboard')
@section('title', 'Verify Certificate')

@section('content')
<div class="mx-auto max-w-2xl rounded-[2rem] border border-slate-200 bg-white/95 p-8 shadow-sm">
    <h1 class="text-2xl font-bold text-slate-900">Certificate Verification</h1>
    <p class="mt-2 text-sm text-slate-500">Use the verification code to confirm a certificate issued by this workspace.</p>

    @if ($cert)
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <div class="text-lg font-bold text-emerald-900">{{ $cert->title }}</div>
            <div class="mt-1 text-sm text-emerald-800">{{ $cert->student?->displayName() ?? 'Learner' }}</div>
            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Verified</div>
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
            No certificate found for code <span class="font-semibold text-slate-900">{{ $code }}</span>.
        </div>
    @endif
</div>
@endsection
