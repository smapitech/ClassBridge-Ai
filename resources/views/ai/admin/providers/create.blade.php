@extends('layouts.dashboard')
@section('title', 'Add AI Provider')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-6">
    <a href="{{ route('ai.admin.providers.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 mb-4 inline-block">&larr; Back to providers</a>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add AI Provider</h1>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('ai.admin.providers.store') }}" class="space-y-6">
        @csrf
        <div class="rounded-lg border border-gray-200 bg-white p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="OpenAI">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="openai">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Provider Type</label>
                    <select name="provider_type" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="openai">OpenAI</option>
                        <option value="deepseek">DeepSeek</option>
                        <option value="custom">Custom (OpenAI Compatible)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="inactive">Inactive</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Base URL</label>
                    <input type="url" name="base_url" value="{{ old('base_url') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="https://api.openai.com/v1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">API Key</label>
                    <input type="password" name="api_key" value="{{ old('api_key') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="sk-...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Default Model</label>
                    <input type="text" name="default_model" value="{{ old('default_model') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="gpt-4o-mini">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Available Models (comma-separated)</label>
                    <input type="text" name="available_models" value="{{ old('available_models') }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="gpt-4o-mini, gpt-4o">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="supports_streaming" value="1" id="streaming" class="rounded border-gray-300 text-indigo-600">
                    <label for="streaming" class="text-sm text-gray-700">Supports Streaming</label>
                </div>
            </div>
        </div>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Create Provider</button>
    </form>
</div>
@endsection