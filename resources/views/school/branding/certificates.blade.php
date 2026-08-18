@extends('layouts.dashboard')
@section('title', 'Certificates')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Issue Certificate</h1>
        <form method="POST" action="{{ route('school.certificates.issue') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Learner</label>
                <select name="student_id" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Template</label>
                <select name="certificate_template_id" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
                    <option value="">Default</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Title</label>
                <input name="title" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Course name</label>
                <input name="course_name" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="4" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900"></textarea>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Issue certificate</button>
            </div>
        </form>
    </div>

    <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900">Recent certificates</h2>
        <div class="mt-5 space-y-3">
            @forelse ($certificates as $certificate)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="text-sm font-semibold text-slate-900">{{ $certificate->title }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ $certificate->student?->displayName() ?? 'Learner' }} · {{ ucfirst($certificate->status) }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No certificates yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
