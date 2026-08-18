@extends('layouts.dashboard')
@section('title', $parent->user->displayName())
@section('content')
<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-2xl font-bold text-gray-900">{{ $parent->user->displayName() }}</h1><p class="text-sm text-gray-500">{{ $parent->user->email }} · <span class="px-2 py-0.5 text-xs rounded-full {{ $parent->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($parent->status) }}</span></p></div>
    <a href="{{ route('school.parents.edit', $parent) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Profile</h2><dl class="grid grid-cols-2 gap-4 text-sm"><div><dt class="text-gray-500">Relationship</dt><dd class="font-medium">{{ $parent->relationship ?? '—' }}</dd></div><div><dt class="text-gray-500">Occupation</dt><dd class="font-medium">{{ $parent->occupation ?? '—' }}</dd></div><div><dt class="text-gray-500">Emergency</dt><dd class="font-medium">{{ $parent->emergency_contact ?? '—' }}</dd></div><div><dt class="text-gray-500">Address</dt><dd class="font-medium">{{ $parent->address ?? '—' }}</dd></div></dl></div>
    <div class="bg-white rounded-xl shadow-sm p-6"><h2 class="text-lg font-semibold text-gray-900 mb-4">Linked Children ({{ $children->count() }})</h2>@forelse($children as $c)<div class="py-2 flex justify-between text-sm"><span><span class="font-medium text-gray-900">{{ $c->first_name }} {{ $c->last_name }}</span> <span class="text-gray-500">({{ $c->relationship ?? 'Child' }})</span></span><form method="POST" action="{{ route('school.parents.unlink-child', [$parent, $c->id]) }}" onsubmit="return confirm('Unlink?')">@csrf @method('DELETE')<button class="text-red-600 text-xs hover:text-red-900">Unlink</button></form></div>@empty<p class="text-sm text-gray-400">No children linked.</p>@endforelse
    @unless(Auth::user()->isTeacher())
    <form method="POST" action="{{ route('school.parents.link-child', $parent) }}" class="mt-4 border-t pt-4 flex gap-3">@csrf
        <select name="student_id" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" required><option value="">Select student...</option>@foreach($allStudents as $s)<option value="{{ $s->id }}">{{ $s->displayName() }}</option>@endforeach</select>
        <input name="relationship" placeholder="Relationship" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Link</button>
    </form>
    @endunless</div>
</div>
@endsection