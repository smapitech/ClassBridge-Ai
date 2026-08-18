@extends('layouts.dashboard')
@section('title', 'Edit ' . $school->displayLabel())
@section('content')
<div class="max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Organization: {{ $school->displayLabel() }}</h1>
    <form method="POST" action="{{ route('super-admin.schools.update', $school) }}" class="bg-white rounded-xl shadow-sm p-8 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Display name *</label>
                <input name="display_name" value="{{ old('display_name', $school->display_name) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('display_name') border-red-500 @enderror">
                @error('display_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Organization type *</label>
                <select name="organization_type" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                    @foreach ($organizationTypes as $option)
                        <option value="{{ $option['value'] }}" {{ old('organization_type', $school->organization_type) === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Preferred teaching mode *</label>
                <select name="preferred_teaching_mode" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                    @foreach ($teachingModes as $option)
                        <option value="{{ $option['value'] }}" {{ old('preferred_teaching_mode', $school->preferred_teaching_mode) === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700">Contact email</label><input name="contact_email" type="email" value="{{ old('contact_email', $school->email) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Phone</label><input name="phone" value="{{ old('phone', $school->phone) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Country</label><input name="country" value="{{ old('country', $school->country) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Timezone *</label><input name="timezone" value="{{ old('timezone', $school->timezone) }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 @error('timezone') border-red-500 @enderror">@error('timezone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium text-gray-700">State</label><input name="state" value="{{ old('state', $school->state) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">City</label><input name="city" value="{{ old('city', $school->city) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700">Address</label><input name="address" value="{{ old('address', $school->address) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Website</label><input name="website" type="url" value="{{ old('website', $school->website) }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700">Status *</label><select name="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"><option value="trial" {{ $school->status === 'trial' ? 'selected' : '' }}>Trial</option><option value="active" {{ $school->status === 'active' ? 'selected' : '' }}>Active</option><option value="suspended" {{ $school->status === 'suspended' ? 'selected' : '' }}>Suspended</option><option value="inactive" {{ $school->status === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
            <div><label class="block text-sm font-medium text-gray-700">Plan *</label><select name="plan_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">@foreach($plans as $p)<option value="{{ $p->id }}" {{ $school->subscription_plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
            <div class="sm:col-span-2"><p class="text-xs text-gray-500">Organization slug: <strong>{{ $school->slug }}</strong> | Owner: <strong>{{ $school->owner?->displayName() ?? 'None' }}</strong> | Created: {{ $school->created_at->format('M j, Y') }}</p></div>
        </div>
        <div class="flex gap-4"><button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update Organization</button><a href="{{ route('super-admin.schools.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a></div>
    </form>
</div>
@endsection
