<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AIProvider;
use App\Services\AI\AIService;
use Illuminate\Http\Request;

class AdminAIProviderController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $providers = AIProvider::latest()->get();
        return view('ai.admin.providers.index', compact('providers'));
    }

    public function create()
    {
        return view('ai.admin.providers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:ai_providers,slug',
            'provider_type' => 'required|in:openai,deepseek,custom',
            'base_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'default_model' => 'nullable|string|max:255',
            'available_models' => 'nullable|string',
            'supports_streaming' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        AIProvider::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'provider_type' => $validated['provider_type'],
            'base_url' => $validated['base_url'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
            'default_model' => $validated['default_model'] ?? null,
            'available_models' => $validated['available_models']
                ? array_map('trim', explode(',', $validated['available_models']))
                : null,
            'supports_streaming' => $request->boolean('supports_streaming'),
            'status' => $validated['status'],
        ]);

        return redirect()->route('ai.admin.providers.index')->with('success', 'Provider created.');
    }

    public function edit(AIProvider $provider)
    {
        return view('ai.admin.providers.edit', compact('provider'));
    }

    public function update(Request $request, AIProvider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider_type' => 'required|in:openai,deepseek,custom',
            'base_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'default_model' => 'nullable|string|max:255',
            'available_models' => 'nullable|string',
            'supports_streaming' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $provider->update([
            'name' => $validated['name'],
            'provider_type' => $validated['provider_type'],
            'base_url' => $validated['base_url'] ?? $provider->base_url,
            'default_model' => $validated['default_model'] ?? $provider->default_model,
            'available_models' => $validated['available_models']
                ? array_map('trim', explode(',', $validated['available_models']))
                : $provider->available_models,
            'supports_streaming' => $request->boolean('supports_streaming'),
            'status' => $validated['status'],
        ]);

        // Only update API key if provided
        if ($request->filled('api_key')) {
            $provider->api_key = $request->api_key;
            $provider->save();
        }

        return redirect()->route('ai.admin.providers.index')->with('success', 'Provider updated.');
    }

    public function toggleStatus(AIProvider $provider)
    {
        $provider->status = $provider->status === 'active' ? 'inactive' : 'active';
        $provider->save();
        return back()->with('success', "{$provider->name} is now {$provider->status}.");
    }

    public function setDefault(AIProvider $provider)
    {
        AIProvider::query()->update(['is_default' => false]);
        $provider->is_default = true;
        $provider->save();
        return back()->with('success', "{$provider->name} set as default provider.");
    }

    public function test(AIProvider $provider)
    {
        $result = $this->aiService->testProvider($provider);
        return back()->with($result['success'] ? 'success' : 'error', $result['message'] . ($result['latency_ms'] ? " (Latency: {$result['latency_ms']}ms)" : ''));
    }
}