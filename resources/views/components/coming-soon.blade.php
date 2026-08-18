@extends('layouts.dashboard')
@section('title', $title ?? 'Coming Soon')

@php
    $moduleTitle = $title ?? 'Coming Soon';
    $moduleDescription = $description ?? 'This module is being prepared and will be available soon.';
    $moduleStatus = $status ?? 'Placeholder module';
    $roleSlug = auth()->user()?->role?->slug;
    $titleKey = strtolower($moduleTitle);

    $primaryActionLabel = $cta_label ?? null;
    $primaryActionHref = $cta_href ?? null;
    $secondaryActionLabel = $secondary_cta_label ?? 'Open Dashboard';
    $secondaryActionHref = $secondary_cta_href ?? (auth()->check() ? route('dashboard') : route('home'));

    $actionMap = [];

    if ($roleSlug === 'super_admin') {
        $actionMap = [
            'subscription plans' => ['Manage Subscription Plans', route('billing.admin.plans')],
            'subscriptions' => ['View Subscriptions', route('billing.admin.subscriptions')],
            'subscription' => ['View Subscriptions', route('billing.admin.subscriptions')],
            'ai providers' => ['Manage AI Providers', route('ai.admin.providers.index')],
            'ai usage' => ['Review AI Usage', route('ai.admin.usage')],
            'organizations' => ['Manage Organizations', route('super-admin.schools.index')],
            'tutors' => ['Review Organizations', route('super-admin.schools.index')],
            'users' => ['Open Platform Dashboard', route('super-admin.dashboard')],
            'audit' => ['Open Platform Dashboard', route('super-admin.dashboard')],
            'settings' => ['Open Platform Dashboard', route('super-admin.dashboard')],
        ];
    } elseif (in_array($roleSlug, ['school_owner', 'school_admin'], true)) {
        $actionMap = [
            'live classroom' => ['Start a Live Lesson', route('live-lessons.create')],
            'live classrooms' => ['Open Live Classroom Hub', route('classrooms.index')],
            'session' => ['Schedule Session', route('live-lessons.create')],
            'students' => ['Create First Student', route('school.students.create')],
            'parents' => ['Add Parent', route('school.parents.create')],
            'teachers' => ['Add Teacher / Tutor', route('school.teachers.create')],
            'homework' => ['Create Homework', route('academic.homeworks.create')],
            'quizzes' => ['Create Quiz', route('academic.quizzes.create')],
            'reports' => ['View Reports', route('academic.reports.index')],
            'branding' => ['Open Branding', route('school.branding')],
            'settings' => ['Open Organization Profile', route('organization.profile')],
            'ai lesson' => ['Generate AI Lesson', route('ai.school.settings')],
            'ai' => ['Generate AI Lesson', route('ai.school.settings')],
            'billing' => ['Open Billing', route('billing.school')],
        ];
    } elseif ($roleSlug === 'teacher') {
        $actionMap = [
            'live classroom' => ['Start a Live Lesson', route('live-lessons.create')],
            'live classrooms' => ['Open Live Classroom Hub', route('classrooms.index')],
            'whiteboard' => ['Open Live Classroom', route('live-interactive-classroom')],
            'text pad' => ['Open Live Classroom', route('live-interactive-classroom')],
            'coding' => ['Join Live Lesson', route('join')],
            'students' => ['View My Students', route('school.students.index')],
            'classes' => ['View My Classes', route('school.classes.index')],
            'homework' => ['Create Homework', route('academic.homeworks.create')],
            'quizzes' => ['Create Quiz', route('academic.quizzes.create')],
            'submissions' => ['Review Submissions', route('coding.assignments.index')],
            'reports' => ['View Reports', route('academic.reports.index')],
            'library' => ['Open Teaching Library', route('library.index')],
            'ai' => ['Generate AI Lesson', route('ai.teacher.index')],
            'lesson' => ['Open Lesson Replays', route('lesson-replays.index')],
        ];
    } elseif ($roleSlug === 'student') {
        $actionMap = [
            'live classrooms' => ['Open Live Session Hub', route('classrooms.index')],
            'live classroom' => ['Join Live Class', route('join')],
            'whiteboard' => ['Join Live Class', route('join')],
            'coding' => ['Open Coding Projects', route('coding.my-submissions')],
            'projects' => ['Open Coding Projects', route('coding.my-submissions')],
            'homework' => ['Open My Homework', route('academic.my-homework')],
            'quizzes' => ['Open Quizzes', route('academic.quizzes.index')],
            'reports' => ['View Progress', route('gamification.my-progress')],
            'badges' => ['View Badges', route('gamification.my-progress')],
            'certificates' => ['View Progress', route('gamification.my-progress')],
            'worksheet' => ['Open Worksheets', route('worksheets.student')],
        ];
    } elseif ($roleSlug === 'parent') {
        $actionMap = [
            'live sessions' => ['Open Live Session Hub', route('classrooms.index')],
            'live session' => ['Open Live Session Hub', route('classrooms.index')],
            'child' => ['Open Parent Dashboard', route('parent.dashboard')],
            'children' => ['Open Parent Dashboard', route('parent.dashboard')],
            'progress' => ['Open Parent Dashboard', route('parent.dashboard')],
            'sessions' => ['Open Live Session Hub', route('classrooms.index')],
            'homework' => ['View Teacher Feedback', route('academic.feedback.index')],
            'quizzes' => ['View Teacher Feedback', route('academic.feedback.index')],
            'reports' => ['Open Parent Dashboard', route('parent.dashboard')],
            'achievements' => ['Open Parent Dashboard', route('parent.dashboard')],
            'messages' => ['View Teacher Feedback', route('academic.feedback.index')],
            'payments' => ['Open Parent Dashboard', route('parent.dashboard')],
            'lesson replay' => ['Open Lesson Replay', route('lesson-replays.index')],
        ];
    }

    if (!$primaryActionLabel || !$primaryActionHref) {
        foreach ($actionMap as $needle => $action) {
            if (str_contains($titleKey, $needle)) {
                $primaryActionLabel = $action[0];
                $primaryActionHref = $action[1];
                break;
            }
        }
    }

    if (!$primaryActionLabel || !$primaryActionHref) {
        $primaryActionLabel = 'Open Dashboard';
        $primaryActionHref = auth()->check() ? route('dashboard') : route('home');
    }

    $statusTone = match (strtolower($moduleStatus)) {
        'ready', 'live', 'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'beta' => 'bg-sky-50 text-sky-700 border-sky-200',
        'placeholder module', 'coming soon', 'placeholder' => 'bg-amber-50 text-amber-800 border-amber-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
@endphp

@section('content')
<div class="px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-2xl shadow-slate-950/10 backdrop-blur-xl">
        <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
            <div class="space-y-8 px-6 py-8 sm:px-8 sm:py-10 lg:px-10">
                <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em] {{ $statusTone }}">
                    <span class="h-2 w-2 rounded-full bg-current opacity-70"></span>
                    {{ $moduleStatus }}
                </div>

                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Module preview</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">{{ $moduleTitle }}</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">{{ $moduleDescription }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Why this exists</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">This page is a professional placeholder so the menu always lands somewhere useful while the final module is being built or expanded.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Next step</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Use the primary action below to continue into the closest live module or the protected classroom workspace.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $primaryActionHref }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        {{ $primaryActionLabel }}
                    </a>
                    <a href="{{ $secondaryActionHref }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                        {{ $secondaryActionLabel }}
                    </a>
                </div>
            </div>

            <div class="border-t border-slate-200/70 bg-[linear-gradient(135deg,_rgba(15,23,42,0.98),_rgba(30,41,59,0.94))] px-6 py-8 text-white sm:px-8 sm:py-10 lg:border-l lg:border-t-0 lg:px-10">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.25em] text-sky-200">Protected workspace</p>
                        <p class="mt-1 text-xl font-black">{{ $moduleTitle }}</p>
                    </div>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold text-white/85">Safe fallback</span>
                </div>

                <div class="mt-6 grid gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-300">Module behaviour</p>
                        <p class="mt-2 text-sm leading-6 text-slate-200">
                            The final module will inherit the dashboard layout, respect role-based access, and connect back to live classrooms, AI tools, and learner progress.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-300">Protected by design</p>
                        <p class="mt-2 text-sm leading-6 text-slate-200">
                            Teachers and learners stay inside ClassBridge AI. No remote desktop access is exposed through this module.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
