@extends('layouts.dashboard')
@section('title', 'Add Student')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Student</h1>
    <form method="POST" action="{{ route('school.students.store') }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-gray-700">First Name *</label><input name="first_name" value="{{ old('first_name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('first_name') border-red-500 @enderror">@error('first_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-gray-700">Last Name *</label><input name="last_name" value="{{ old('last_name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('last_name') border-red-500 @enderror">@error('last_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Email *</label><input name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('email') border-red-500 @enderror">@error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Password *</label><input name="password" type="password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('password') border-red-500 @enderror">@error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-gray-700">Admission Number</label><input name="admission_number" value="{{ old('admission_number') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Date of Birth</label><input name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Gender</label><select name="gender" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="">—</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700">Learning Level</label><input name="learning_level" value="{{ old('learning_level') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Class</label><select name="class_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        </div>
        <div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create Student</button><a href="{{ route('school.students.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
    </form>
</div>
@endsection