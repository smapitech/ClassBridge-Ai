@extends('layouts.dashboard')
@section('title', 'Edit Student')
@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit: {{ $student->user->displayName() }}</h1>
    <form method="POST" action="{{ route('school.students.update', $student) }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-medium text-gray-700">First Name *</label><input name="first_name" value="{{ old('first_name', $student->user->first_name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Last Name *</label><input name="last_name" value="{{ old('last_name', $student->user->last_name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Admission Number</label><input name="admission_number" value="{{ old('admission_number', $student->admission_number) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Date of Birth</label><input name="date_of_birth" type="date" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Gender</label><select name="gender" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="">—</option><option value="Male" {{ $student->gender === 'Male' ? 'selected' : '' }}>Male</option><option value="Female" {{ $student->gender === 'Female' ? 'selected' : '' }}>Female</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700">Learning Level</label><input name="learning_level" value="{{ old('learning_level', $student->learning_level) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Class</label><select name="class_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="">—</option>@foreach($classes as $c)<option value="{{ $c->id }}" {{ $student->class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-gray-700">Status *</label><select name="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="active" {{ $student->status === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ $student->status === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
        </div>
        <div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update</button><a href="{{ route('school.students.show', $student) }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
    </form>
</div>
@endsection