@extends('layouts.dashboard')
@section('title', 'Live Interactive Classroom')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Central module"
        title="Live Interactive Classroom"
        description="Teachers can create sessions, students can join by code, and every interaction stays inside the ClassBridge AI classroom."
    >
        <x-slot name="actions">
            @if(Auth::user()->isTeacher() || Auth::user()->isSchoolAdmin() || Auth::user()->isSchoolOwner())
                <x-primary-button href="{{ route('live-lessons.create') }}">
                    Start a Live Lesson
                </x-primary-button>
            @endif
            <x-secondary-button href="{{ route('join') }}">
                Join by code
            </x-secondary-button>
        </x-slot>
    </x-page-header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Visibility</p>
            <p class="mt-3 text-2xl font-black text-slate-900">Teacher and learner share the same room</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Privacy</p>
            <p class="mt-3 text-2xl font-black text-slate-900">No access to the private device</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Tools</p>
            <p class="mt-3 text-2xl font-black text-slate-900">Whiteboard, pointers, notes, chat</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Modes</p>
            <p class="mt-3 text-2xl font-black text-slate-900">Teaching and coding in one platform</p>
        </div>
    </section>

    <x-data-table-wrapper title="Session list" description="Create, schedule, and launch live lessons.">
        <x-slot name="actions">
            <x-status-badge tone="info">{{ $classrooms->total() }} sessions</x-status-badge>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($classrooms as $classroom)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $classroom->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $classroom->course?->name ?? $classroom->classe?->name ?? 'Unassigned course' }}{{ $classroom->subject?->name ? ' - ' . $classroom->subject->name : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $classroom->teacher->displayName() }}</td>
                            <td class="px-6 py-4"><code class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $classroom->room_code }}</code></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $classroom->status === 'live' ? 'bg-rose-50 text-rose-700' : ($classroom->status === 'scheduled' ? 'bg-sky-50 text-sky-700' : ($classroom->status === 'ended' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700')) }}">
                                    {{ ucfirst($classroom->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('classrooms.show', $classroom) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                    {{ $classroom->status === 'live' ? 'Join now' : 'Open' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                No live sessions yet.
                                @if(Auth::user()->isTeacher() || Auth::user()->isSchoolAdmin() || Auth::user()->isSchoolOwner())
                                    <a href="{{ route('live-lessons.create') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">Start a Live Lesson</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-data-table-wrapper>

    <div>{{ $classrooms->links() }}</div>
</div>
@endsection
