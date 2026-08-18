@extends('layouts.dashboard')
@section('title', 'Edit Parent')
@section('content')
<div class="max-w-2xl"><h1 class="text-2xl font-bold text-gray-900 mb-6">Edit: {{ $parent->user->displayName() }}</h1>
<form method="POST" action="{{ route('school.parents.update', $parent) }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">@csrf @method('PUT')
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div><label class="block text-sm font-medium text-gray-700">First Name *</label><input name="first_name" value="{{ old('first_name', $parent->user->first_name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Last Name *</label><input name="last_name" value="{{ old('last_name', $parent->user->last_name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Relationship</label><input name="relationship" value="{{ old('relationship', $parent->relationship) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Occupation</label><input name="occupation" value="{{ old('occupation', $parent->occupation) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Emergency Contact</label><input name="emergency_contact" value="{{ old('emergency_contact', $parent->emergency_contact) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Status *</label><select name="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="active" {{ $parent->status === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ $parent->status === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Address</label><textarea name="address" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('address', $parent->address) }}</textarea></div>
</div>
<div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update</button><a href="{{ route('school.parents.show', $parent) }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
</form></div>
@endsection