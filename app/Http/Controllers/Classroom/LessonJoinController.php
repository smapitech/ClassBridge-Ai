<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\CodingSession;
use App\Services\Classroom\LiveClassroomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonJoinController extends Controller
{
    public function __construct(protected LiveClassroomService $lessonService)
    {
    }

    public function show(Request $request): View
    {
        return view('join', [
            'prefillRoomCode' => $request->query('room_code')
                ?? $request->query('join_code')
                ?? $request->session()->get('pending_join_room_code'),
        ]);
    }

    public function showRoom(Request $request, string $roomCode): mixed
    {
        $request->session()->put('pending_join_room_code', $roomCode);

        if (! Auth::check()) {
            return response()->view('join', [
                'prefillRoomCode' => $roomCode,
                'invitationLinkMode' => true,
            ]);
        }

        return $this->joinRoom($request, $roomCode);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_code' => ['nullable', 'string', 'max:50'],
            'join_code' => ['nullable', 'string', 'max:50'],
        ]);

        $roomCode = trim((string) ($validated['room_code'] ?: $validated['join_code'] ?: ''));

        if ($roomCode === '') {
            return back()->with('error', 'Please enter a room code.');
        }

        if (! Auth::check()) {
            $request->session()->put('pending_join_room_code', $roomCode);

            return redirect()->route('login')->with('error', 'Please log in to join the live lesson.');
        }

        return $this->joinRoom($request, $roomCode);
    }

    protected function joinRoom(Request $request, string $roomCode): RedirectResponse
    {
        $normalizedRoomCode = Str::upper(trim($roomCode));
        $classroom = $this->lessonService->resolveRoomByCode($normalizedRoomCode);

        $user = Auth::user();

        if ($classroom) {
            if (! in_array($classroom->status, ['scheduled', 'live'], true)) {
                return back()->withInput()->with('error', 'This lesson is not joinable right now.');
            }

            if (! $this->lessonService->canAccessLesson($classroom, $user)) {
                abort(403);
            }

            $join = $this->lessonService->joinLesson($classroom, $user);

            return redirect()
                ->route('classrooms.show', $join['classroom'])
                ->with('success', 'You have joined the live lesson.');
        }

        $codingSession = CodingSession::query()
            ->whereRaw('UPPER(join_code) = ?', [$normalizedRoomCode])
            ->first();

        if (! $codingSession) {
            return back()->withInput()->with('error', 'We could not find a live lesson with that room code.');
        }

        if (! $this->canAccessCodingSession($codingSession, $user)) {
            abort(403);
        }

        return redirect()
            ->route('coding.sessions.show', $codingSession)
            ->with('success', 'You have joined the coding workspace.');
    }

    protected function canAccessCodingSession(CodingSession $session, $user): bool
    {
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
}
