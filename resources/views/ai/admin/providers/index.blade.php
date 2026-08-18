@extends('layouts.dashboard')
@section('title', 'AI Providers')

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">AI Providers</h1>
        <a href="{{ route('ai.admin.providers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">+ Add Provider</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @forelse($providers as $provider)
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $provider->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $provider->slug }} · {{ $provider->provider_type }}</p>
                </div>
                <div class="flex gap-1">
                    @if($provider->is_default)
                        <span class="inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Default</span>
                    @endif
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $provider->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($provider->status) }}
                    </span>
                    @if(!$provider->hasApiKey())
                        <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">No Key</span>
                    @endif
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-2 text-sm mb-4">
                <div><dt class="text-gray-500">Model</dt><dd class="font-medium">{{ $provider->default_model ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">API Key</dt><dd class="font-mono text-xs">{{ $provider->maskedApiKey() }}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">Base URL</dt><dd class="text-xs font-mono truncate">{{ $provider->base_url ?? 'Default' }}</dd></div>
            </dl>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('ai.admin.providers.edit', $provider) }}" class="rounded bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200">Edit</a>
                <form method="POST" action="{{ route('ai.admin.providers.test', $provider) }}" class="inline">@csrf
                    <button class="rounded bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-100">Test</button>
                </form>
                <form method="POST" action="{{ route('ai.admin.providers.set-default', $provider) }}" class="inline">@csrf
                    <button class="rounded bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-100">Set Default</button>
                </form>
                <form method="POST" action="{{ route('ai.admin.providers.toggle-status', $provider) }}" class="inline">@csrf
                    <button class="rounded px-3 py-1 text-xs font-medium {{ $provider->status === 'active' ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                        {{ $provider->status === 'active' ? 'Disable' : 'Enable' }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-2 rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center">
            <p class="text-gray-500">No AI providers configured yet.</p>
            <a href="{{ route('ai.admin.providers.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">Add your first provider</a>
        </div>
        @endforelse
    </div>

    <div class="mt-6 rounded-lg bg-blue-50 p-4 text-sm text-blue-700">
        <strong>Provider Hierarchy:</strong> The system uses the <strong>Default</strong> provider for all AI generation. If a school has override enabled in AI Settings, that school can choose a different provider.
    </div>
</div>
@endsection