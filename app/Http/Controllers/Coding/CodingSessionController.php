<?php

namespace App\Http\Controllers\Coding;

use App\Http\Controllers\Controller;
use App\Models\CodingSession;
use App\Models\CodingSessionParticipant;
use App\Services\Coding\CodingStudioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CodingSessionController extends Controller
{
    public function __construct(protected CodingStudioService $studio) {}

    protected function currentParticipant(CodingSession $session): ?CodingSessionParticipant
    {
        return $session->participants()
            ->where('user_id', Auth::id())
            ->latest('joined_at')
            ->first();
    }

    protected function canAccess(CodingSession $session): bool
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->school_id || $session->school_id !== $user->school_id) {
            return false;
        }

        if ($session->teacher_id === $user->id || $session->student_id === $user->id) {
            return true;
        }

        if ($user->isParent()) {
            $childIds = $user->children()->pluck('users.id');
            return $childIds->contains($session->student_id);
        }

        return $session->participants()->where('user_id', $user->id)->exists();
    }

    protected function ensureAccess(CodingSession $session): void
    {
        abort_unless($this->canAccess($session), 403);
    }

    public function show(CodingSession $session)
    {
        $this->ensureAccess($session);

        $session->load([
            'teacher',
            'student',
            'class',
            'subject',
            'assignment',
            'participants.user',
            'files',
            'messages.user',
            'events.user',
        ]);

        $isTeacher = $this->studio->isTeacher($session, Auth::user(), $this->currentParticipant($session));
        $role = $isTeacher ? 'teacher' : (Auth::user()->isParent() ? 'observer' : 'student');
        $participant = $this->studio->ensureParticipant(
            $session,
            Auth::user(),
            $role,
            $this->studio->participantPermissions($session, Auth::user(), $this->currentParticipant($session))
        );

        $session->refresh()->load([
            'participants.user',
            'files',
            'messages.user',
            'events.user',
            'teacher',
            'student',
            'class',
            'subject',
            'assignment',
        ]);

        $joinLink = route('coding.sessions.join.form', ['join_code' => $session->join_code]);
        $currentFile = $session->activeFile();
        $permissions = $this->studio->participantPermissions($session, Auth::user(), $participant);
        $sessionSnapshot = $this->studio->workspaceSnapshot($session);

        return view('coding.studio', [
            'session' => $session,
            'isTeacher' => $isTeacher,
            'myParticipant' => $participant,
            'joinLink' => $joinLink,
            'currentFile' => $currentFile,
            'permissions' => $permissions,
            'sessionSnapshot' => $sessionSnapshot,
        ]);
    }

    public function joinForm(Request $request)
    {
        return view('coding.join', [
            'prefillJoinCode' => $request->query('join_code'),
        ]);
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'join_code' => ['required', 'string'],
        ]);

        $session = CodingSession::whereRaw('UPPER(join_code) = ?', [Str::upper(trim($validated['join_code']))])->first();

        if (! $session) {
            return back()->with('error', 'We could not find a coding session with that code.');
        }

        if (! $this->canAccess($session)) {
            return back()->with('error', 'You are not authorized to join this coding session.');
        }

        return redirect()->route('coding.sessions.show', $session);
    }

    public function start(CodingSession $session)
    {
        $this->ensureAccess($session);

        abort_unless($this->studio->isTeacher($session, Auth::user(), $this->currentParticipant($session)), 403);

        $session->status = 'live';
        $session->started_at = $session->started_at ?: now();
        $session->save();

        $this->studio->recordEvent($session, 'session.status.changed', Auth::user(), 'Session started', 'The live coding session is now online.', [
            'status' => 'live',
        ]);

        return back()->with('success', 'Coding session started.');
    }

    public function end(CodingSession $session)
    {
        $this->ensureAccess($session);

        abort_unless($this->studio->isTeacher($session, Auth::user(), $this->currentParticipant($session)), 403);

        $session->endSession();

        $this->studio->recordEvent($session, 'session.status.changed', Auth::user(), 'Session ended', 'The live coding session has been closed.', [
            'status' => 'ended',
        ]);

        return redirect()->route('coding.sessions.show', $session)->with('success', 'Coding session ended.');
    }
}
