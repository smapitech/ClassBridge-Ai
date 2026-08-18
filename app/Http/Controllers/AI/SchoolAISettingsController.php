<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AISetting;
use App\Models\AIProvider;
use App\Models\AIUsageLog;
use App\Models\AIGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolAISettingsController extends Controller
{
    public function settings()
    {
        $schoolId = Auth::user()->school_id;
        $settings = AISetting::forSchool($schoolId);
        $globalSettings = AISetting::global();
        $providers = AIProvider::active()->get();
        $usage = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->count();
        $tokens = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->sum('total_tokens');
        $cost = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->sum('cost_estimate');

        return view('ai.school.settings', compact('settings', 'globalSettings', 'providers', 'usage', 'tokens', 'cost'));
    }

    public function updateSettings(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $settings = AISetting::forSchool($schoolId);

        $validated = $request->validate([
            'default_provider_id' => 'nullable|exists:ai_providers,id',
        ]);

        $settings->update($validated);

        return back()->with('success', 'AI settings updated.');
    }

    public function usage(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $logs = AIUsageLog::where('school_id', $schoolId)->with('user', 'provider')->latest()->paginate(50);
        $totalTokens = AIUsageLog::where('school_id', $schoolId)->success()->sum('total_tokens');
        $monthlyTokens = AIUsageLog::where('school_id', $schoolId)->success()->thisMonth()->sum('total_tokens');

        return view('ai.school.usage', compact('logs', 'totalTokens', 'monthlyTokens'));
    }

    public function history(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $query = AIGeneration::where('school_id', $schoolId)->with('user', 'provider')->latest();
        if ($request->type) $query->where('type', $request->type);
        $generations = $query->paginate(50);

        return view('ai.school.history', compact('generations'));
    }
}