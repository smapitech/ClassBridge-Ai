<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AIProvider;
use App\Models\AISetting;
use App\Models\AIGeneration;
use App\Models\AIUsageLog;
use App\Models\School;
use Illuminate\Http\Request;

class AdminAISettingsController extends Controller
{
    public function settings()
    {
        $settings = AISetting::global() ?? new AISetting();
        $providers = AIProvider::active()->get();
        $schools = School::with('aiSetting')->get();
        return view('ai.admin.settings', compact('settings', 'providers', 'schools'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'ai_enabled' => 'boolean',
            'allow_teacher_ai' => 'boolean',
            'allow_school_override' => 'boolean',
            'default_provider_id' => 'nullable|exists:ai_providers,id',
            'monthly_generation_limit' => 'nullable|integer|min:1',
            'monthly_token_limit' => 'nullable|integer|min:1',
            'monthly_cost_limit' => 'nullable|numeric|min:0',
        ]);

        $settings = AISetting::global() ?? new AISetting();
        $settings->fill($validated);
        $settings->school_id = null;
        $settings->save();

        return back()->with('success', 'Global AI settings updated.');
    }

    public function updateSchoolSettings(Request $request, School $school)
    {
        $validated = $request->validate([
            'ai_enabled' => 'boolean',
            'allow_teacher_ai' => 'boolean',
            'default_provider_id' => 'nullable|exists:ai_providers,id',
            'monthly_generation_limit' => 'nullable|integer|min:1',
            'monthly_token_limit' => 'nullable|integer|min:1',
        ]);

        $settings = AISetting::forSchool($school->id);
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'School AI settings updated.');
    }

    public function usage(Request $request)
    {
        $query = AIUsageLog::with(['school', 'user', 'provider'])->latest();

        if ($request->school_id) $query->where('school_id', $request->school_id);
        if ($request->request_status) $query->where('request_status', $request->request_status);
        if ($request->provider_id) $query->where('provider_id', $request->provider_id);

        $logs = $query->paginate(50);
        $schools = School::orderBy('name')->get();
        $providers = AIProvider::all();
        $totalTokens = AIUsageLog::success()->sum('total_tokens');
        $totalCost = AIUsageLog::success()->sum('cost_estimate');

        return view('ai.admin.usage', compact('logs', 'schools', 'providers', 'totalTokens', 'totalCost'));
    }

    public function history(Request $request)
    {
        $query = AIGeneration::with(['user', 'school', 'provider'])->latest();

        if ($request->type) $query->where('type', $request->type);
        if ($request->status) $query->where('status', $request->status);
        if ($request->school_id) $query->where('school_id', $request->school_id);
        if ($request->provider_id) $query->where('provider_id', $request->provider_id);

        $generations = $query->paginate(50);
        $providers = AIProvider::all();
        $schools = School::orderBy('name')->get();

        return view('ai.admin.history', compact('generations', 'providers', 'schools'));
    }
}