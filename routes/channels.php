<?php

use App\Models\CodingSession;
use App\Models\CodingSessionParticipant;
use App\Models\ClassroomParticipant;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels - ClassBridge AI
|--------------------------------------------------------------------------
|
| Only authenticated users who are participants in the classroom session
| can join the private classroom channel.
|
*/

Broadcast::channel('classroom.{sessionId}', function ($user, $sessionId) {
    // Must be authenticated
    if (!$user) return false;

    // Check if user is a participant in this session
    return ClassroomParticipant::where('classroom_session_id', $sessionId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('coding.{sessionId}', function ($user, $sessionId) {
    if (! $user) {
        return false;
    }

    $session = CodingSession::find($sessionId);

    if (! $session) {
        return false;
    }

    if ($user->isSuperAdmin()) {
        return true;
    }

    if (! $user->school_id || $session->school_id !== $user->school_id) {
        return false;
    }

    if ($session->teacher_id === $user->id || $session->student_id === $user->id) {
        return true;
    }

    return CodingSessionParticipant::where('coding_session_id', $session->id)
        ->where('user_id', $user->id)
        ->exists();
});
