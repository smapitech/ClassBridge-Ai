@extends('layouts.dashboard')
@section('title', 'Add Teacher')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Teacher</h1>
    <form method="POST" action="{{ route('school.teachers.store') }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-gray-700">First Name *</label><input name="first_name" value="{{ old('first_name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('first_name') border-red-500 @enderror">@error('first_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-gray-700">Last Name *</label><input name="last_name" value="{{ old('last_name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('last_name') border-red-500 @enderror">@error('last_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Email *</label><input name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('email') border-red-500 @enderror">@error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Password *</label><input name="password" type="password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('password') border-red-500 @enderror">@error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-gray-700">Qualification</label><input name="qualification" value="{{ old('qualification') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Specialization</label><input name="specialization" value="{{ old('specialization') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Years of Experience</label><input name="years_of_experience" type="number" min="0" value="{{ old('years_of_experience') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Bio</label><textarea name="bio" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('bio') }}</textarea></div>
        </div>
        <div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create Teacher</button><a href="{{ route('school.teachers.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
    </form>
</div>
@endsection