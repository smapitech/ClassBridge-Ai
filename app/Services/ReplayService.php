<?php namespace App\Services;

use App\Models\ClassroomActivityEvent;
use App\Models\ClassroomSession;
use App\Models\LessonReplay;
use Illuminate\Support\Facades\Auth;

class ReplayService
{
    /** Log an activity event for the classroom timeline */
    public static function log(int $sessionId, string $eventType, ?string $title = null, ?string $description = null, ?array $data = null, ?int $userId = null, ?int $liveClassroomId = null): void
    {
        $session = ClassroomSession::find($sessionId);
        if (!$session) return;

        ClassroomActivityEvent::create([
            'school_id' => $session->school_id,
            'classroom_session_id' => $sessionId,
            'live_classroom_id' => $liveClassroomId ?? $session->live_classroom_id,
            'user_id' => $userId ?? (Auth::check() ? Auth::id() : null),
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'event_data' => $data,
            'occurred_at' => now(),
        ]);
    }

    /** Generate a lesson replay from all session activity events */
    public static function generateReplay(ClassroomSession $session, string $title, ?string $summary = null, string $visibility = 'teacher_only'): LessonReplay
    {
        $events = ClassroomActivityEvent::forSession($session->id)->orderBy('occurred_at')->get();

        return LessonReplay::create([
            'school_id' => $session->school_id,
            'classroom_session_id' => $session->id,
            'live_classroom_id' => $session->live_classroom_id,
            'title' => $title,
            'summary' => $summary,
            'replay_data' => ['events' => $events->toArray(), 'participant_count' => $session->participants()->count(), 'duration' => $session->started_at && $session->ended_at ? $session->ended_at->diffInMinutes($session->started_at) . ' min' : 'N/A'],
            'visibility' => $visibility,
            'status' => 'ready',
            'created_by' => Auth::id(),
        ]);
    }
}