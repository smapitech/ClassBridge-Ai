@extends('layouts.dashboard')
@section('title', 'Organization Branding')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
    <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Branding & Identity</h1>
        <p class="mt-2 text-sm text-slate-500">Control how the workspace looks in emails, certificates, and parent-facing materials.</p>

        <form method="POST" action="{{ route('school.branding.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Primary color</label>
                    <input name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Secondary color</label>
                    <input name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Accent color</label>
                    <input name="accent_color" value="{{ old('accent_color', $settings->accent_color) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Portal theme</label>
                    <input name="portal_theme" value="{{ old('portal_theme', $settings->portal_theme) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Logo</label>
                    <input name="logo" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Favicon</label>
                    <input name="favicon" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Email sender name</label>
                    <input name="email_sender_name" value="{{ old('email_sender_name', $settings->email_sender_name) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Support email</label>
                    <input name="support_email" type="email" value="{{ old('support_email', $settings->support_email) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700">Certificate signature</label>
                    <input name="certificate_signature" value="{{ old('certificate_signature', $settings->certificate_signature) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                </div>
            </div>

            <button type="submit" class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Save branding</button>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Preview</h2>
            <div class="mt-4 rounded-2xl bg-slate-950 p-5 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">ClassBridge AI</p>
                <p class="mt-2 text-lg font-bold">{{ Auth::user()->school?->displayLabel() ?? 'Organization' }}</p>
                <p class="mt-2 text-sm text-slate-300">Identity used across certificates and parent-facing communications.</p>
            </div>
            @if ($settings->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" class="mt-4 h-20 w-auto rounded-xl object-contain" alt="Logo preview">
            @endif
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6">
            <h2 class="text-lg font-bold text-slate-900">Why it matters</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">Branding keeps your school, tutoring center, or private tutoring business consistent across parent reports, certificates, and the learning workspace.</p>
        </div>
    </div>
</div>
@endsection
