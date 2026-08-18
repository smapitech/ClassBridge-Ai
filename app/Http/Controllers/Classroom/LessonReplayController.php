<?php namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\ClassroomActivityEvent;
use App\Models\ClassroomSession;
use App\Models\LessonReplay;
use App\Services\ReplayService;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonReplayController extends Controller
{
    /** List replays for a session */
    public function index(Request $r)
    {
        $user = Auth::user();
        $query = LessonReplay::forSchool($user->school_id)->with(['session.classroom', 'creator'])->latest();

        if ($r->session_id) $query->where('classroom_session_id', $r->session_id);

        // Student: only their classes
        if ($user->isStudent()) {
            $query->whereIn('visibility', ['student', 'parent', 'school_admin']);
        }

        // Parent: only published to parent
        if ($user->isParent()) {
            $query->whereIn('visibility', ['parent', 'school_admin']);
        }

        return view('lesson-replays.index', ['replays' => $query->paginate(20)]);
    }

    /** Show activity timeline for a session */
    public function timeline(ClassroomSession $session)
    {
        if ($session->school_id !== Auth::user()->school_id) abort(403);

        $events = ClassroomActivityEvent::forSession($session->id)->with('user')->orderBy('occurred_at')->get();
        $replay = LessonReplay::where('classroom_session_id', $session->id)->first();

        return view('lesson-replays.timeline', compact('session', 'events', 'replay'));
    }

    /** Generate a replay from session events */
    public function generate(Request $r, ClassroomSession $session)
    {
        if ($session->school_id !== Auth::user()->school_id) abort(403);
        if (!Auth::user()->isTeacher() && !Auth::user()->isSchoolAdmin() && !Auth::user()->isSchoolOwner()) abort(403);

        $r->validate(['title' => 'required|string|max:255']);

        // AI summary
        $summary = $r->input('summary');
        if ($r->boolean('use_ai')) {
            try {
                $events = ClassroomActivityEvent::forSession($session->id)->count();
                $aiService = app(AIService::class);
                $summary = $aiService->generate(
                    "Write a parent-friendly lesson summary for '{$r->title}'. " .
                    "Session had $events activities. Include: what was taught, what students practiced, " .
                    "and a home practice recommendation. Keep it friendly and under 200 words."
                );
            } catch (\Exception $e) {
                $summary = 'AI summary unavailable.';
            }
        }

        $replay = ReplayService::generateReplay($session, $r->title, $summary, $r->input('visibility', 'teacher_only'));

        return redirect()->route('lesson-replays.show', $replay)->with('success', 'Lesson replay generated!');
    }

    /** View a replay */
    public function show(LessonReplay $replay)
    {
        if ($replay->school_id !== Auth::user()->school_id) abort(403);

        // Check visibility
        $user = Auth::user();
        if ($replay->visibility === 'teacher_only' && !$user->isTeacher() && !$user->isSchoolAdmin() && !$user->isSchoolOwner()) abort(403);
        if ($replay->visibility === 'student' && $user->isParent()) abort(403);

        $replay->load(['session', 'creator']);
        $events = ClassroomActivityEvent::forSession($replay->classroom_session_id)->with('user')->orderBy('occurred_at')->get();

        return view('lesson-replays.show', compact('replay', 'events'));
    }

    /** Update replay visibility/status */
    public function update(Request $r, LessonReplay $replay)
    {
        if ($replay->school_id !== Auth::user()->school_id) abort(403);
        $replay->update($r->only(['visibility', 'status', 'summary']));
        return back()->with('success', 'Replay updated.');
    }

    /** Delete a replay */
    public function destroy(LessonReplay $replay)
    {
        if ($replay->school_id !== Auth::user()->school_id) abort(403);
        $replay->delete();
        return redirect()->route('lesson-replays.index')->with('success', 'Replay deleted.');
    }
}