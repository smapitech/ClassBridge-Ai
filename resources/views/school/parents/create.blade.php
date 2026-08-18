@extends('layouts.dashboard')
@section('title', 'Add Parent')
@section('content')
<div class="max-w-2xl"><h1 class="text-2xl font-bold text-gray-900 mb-6">Add Parent</h1>
<form method="POST" action="{{ route('school.parents.store') }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">@csrf
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div><label class="block text-sm font-medium text-gray-700">First Name *</label><input name="first_name" value="{{ old('first_name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('first_name') border-red-500 @enderror">@error('first_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium text-gray-700">Last Name *</label><input name="last_name" value="{{ old('last_name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('last_name') border-red-500 @enderror">@error('last_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Email *</label><input name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('email') border-red-500 @enderror">@error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Password *</label><input name="password" type="password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('password') border-red-500 @enderror">@error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-medium text-gray-700">Relationship</label><input name="relationship" value="{{ old('relationship') }}" placeholder="e.g. Father" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Occupation</label><input name="occupation" value="{{ old('occupation') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700">Emergency Contact</label><input name="emergency_contact" value="{{ old('emergency_contact') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
    <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Address</label><textarea name="address" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('address') }}</textarea></div>
</div>
<div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create Parent</button><a href="{{ route('school.parents.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
</form></div>
@endsection