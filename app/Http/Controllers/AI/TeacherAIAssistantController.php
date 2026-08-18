<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AIGeneration;
use App\Models\User;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherAIAssistantController extends Controller
{
    protected AIService $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /** Show the AI assistant dashboard with all tabs */
    public function index()
    {
        $history = AIGeneration::where('user_id', Auth::id())
            ->with('provider')
            ->latest()
            ->limit(20)
            ->get();

        $usageThisMonth = \App\Models\AIUsageLog::where('user_id', Auth::id())
            ->success()
            ->thisMonth()
            ->count();

        return view('ai.teacher.assistant', compact('history', 'usageThisMonth'));
    }

    /** Handle AI generation requests */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:curriculum,lesson_plan,examples,quiz,homework,progress_report,general',
            'prompt' => 'required|string|max:10000',
            'title' => 'nullable|string|max:255',
        ]);

        $type = $request->type;
        $title = $request->title ?? ucfirst(str_replace('_', ' ', $type));

        $messages = [
            ['role' => 'user', 'content' => $request->prompt],
        ];

        $result = $this->aiService->generate(Auth::user(), $type, $messages, [
            'title' => $title,
            'metadata' => $request->only(['subject', 'age_group', 'level']),
        ]);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if (!$result['success']) {
            return back()->with('error', $result['error'])->withInput();
        }

        return back()->with([
            'result' => $result['content'],
            'provider_name' => $result['provider'],
            'model' => $result['model'],
            'usage' => $result['usage'],
        ])->withInput();
    }

    /** Save generated content */
    public function save(Request $request)
    {
        $request->validate([
            'generation_id' => 'nullable|exists:ai_generations,id',
            'content' => 'required|string',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
        ]);

        // Save as a general AI generation with metadata
        AIGeneration::create([
            'school_id' => Auth::user()->school_id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'title' => $request->title,
            'prompt' => 'Saved content',
            'response' => $request->content,
            'status' => 'completed',
        ]);

        return back()->with('success', 'Content saved successfully!');
    }

    /** Show generation history */
    public function history()
    {
        $generations = AIGeneration::where('user_id', Auth::id())
            ->with('provider')
            ->latest()
            ->paginate(30);

        return view('ai.teacher.history', compact('generations'));
    }
}