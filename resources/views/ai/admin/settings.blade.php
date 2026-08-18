@extends('layouts.dashboard')
@section('title', 'AI Settings')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">AI Settings</h1>

    @if(session('success'))<div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>@endif

    {{-- Global Settings --}}
    <form method="POST" action="{{ route('ai.admin.settings.update') }}" class="mb-8">
        @csrf @method('PUT')
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Global AI Settings</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ai_enabled" value="1" {{ $settings->ai_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                    <label class="text-sm text-gray-700">AI System Enabled</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="allow_teacher_ai" value="1" {{ $settings->allow_teacher_ai ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                    <label class="text-sm text-gray-700">Allow Teacher AI Access</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="allow_school_override" value="1" {{ $settings->allow_school_override ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                    <label class="text-sm text-gray-700">Allow School Provider Override</label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Default Provider</label>
                    <select name="default_provider_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">System Default</option>
                        @foreach($providers as $p)<option value="{{ $p->id }}" {{ $settings->default_provider_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-700">Monthly Generation Limit</label><input type="number" name="monthly_generation_limit" value="{{ $settings->monthly_generation_limit }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-700">Monthly Token Limit</label><input type="number" name="monthly_token_limit" value="{{ $settings->monthly_token_limit }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-700">Monthly Cost Limit ($)</label><input type="number" step="0.01" name="monthly_cost_limit" value="{{ $settings->monthly_cost_limit }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
            </div>
            <button type="submit" class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save Global Settings</button>
        </div>
    </form>

    {{-- Per-School Overrides --}}
    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 font-semibold text-sm text-gray-900">Per-School Settings</div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-4 py-2 text-left">School</th><th class="px-4 py-2 text-left">AI Enabled</th><th class="px-4 py-2 text-left">Provider</th><th class="px-4 py-2 text-left">Monthly Gen Limit</th><th class="px-4 py-2 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($schools as $school)
                <tr>
                    <td class="px-4 py-2 font-medium">{{ $school->name }}</td>
                    <td class="px-4 py-2">{{ $school->aiSetting?->ai_enabled ? '✅' : '❌' }}</td>
                    <td class="px-4 py-2 text-xs">{{ $school->aiSetting?->defaultProvider?->name ?? 'Default' }}</td>
                    <td class="px-4 py-2">{{ $school->aiSetting?->monthly_generation_limit ?? '—' }}</td>
                    <td class="px-4 py-2 text-right">
                        <button onclick="document.getElementById('school-form-{{ $school->id }}').classList.toggle('hidden')" class="text-indigo-600 hover:text-indigo-800 text-xs">Edit</button>
                    </td>
                </tr>
                <tr id="school-form-{{ $school->id }}" class="hidden bg-gray-50">
                    <td colspan="5" class="px-4 py-2">
                        <form method="POST" action="{{ route('ai.admin.settings.update-school', $school) }}" class="flex flex-wrap gap-3 items-end">@csrf @method('PUT')
                            <div class="flex items-center gap-2"><input type="checkbox" name="ai_enabled" value="1" {{ $school->aiSetting?->ai_enabled ? 'checked' : '' }}><label class="text-xs">AI On</label></div>
                            <div><select name="default_provider_id" class="rounded border-gray-300 text-xs px-2 py-1"><option value="">Default</option>@foreach($providers as $p)<option value="{{ $p->id }}" {{ $school->aiSetting?->default_provider_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
                            <div><input type="number" name="monthly_generation_limit" value="{{ $school->aiSetting?->monthly_generation_limit }}" class="rounded border-gray-300 text-xs px-2 py-1 w-24" placeholder="Gen limit"></div>
                            <div><input type="number" name="monthly_token_limit" value="{{ $school->aiSetting?->monthly_token_limit }}" class="rounded border-gray-300 text-xs px-2 py-1 w-24" placeholder="Token limit"></div>
                            <button class="rounded bg-indigo-600 px-3 py-1 text-xs text-white hover:bg-indigo-700">Save</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection