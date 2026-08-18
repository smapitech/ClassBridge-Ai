<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\LiveClassroom;
use App\Models\Subject;
use App\Services\Classroom\LiveClassroomService;
use App\Services\Classroom\ClassroomRealtimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    public function __construct(
        protected ClassroomRealtimeService $realtime,
        protected LiveClassroomService $lessonService,
    ) {}

    protected function schoolId(): int { return Auth::user()->school_id; }

    /**
     * List all classrooms for this school.
     */
    public function index()
    {
        $user = Auth::user();

        $query = LiveClassroom::forSchool($this->schoolId())
            ->with(['teacher', 'course', 'classe', 'subject', 'creator'])
            ->latest();

        if ($user->isSuperAdmin()) {
            // Platform admins can see every school room.
        } elseif ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->isStudent()) {
            $query->whereHas('classe.students', fn ($studentQuery) => $studentQuery->whereKey($user->id));
        } elseif ($user->isParent()) {
            $childIds = $user->children()->pluck('users.id');

            if ($childIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('classe.students', fn ($studentQuery) => $studentQuery->whereKey($childIds->all()));
            }
        }

        $classrooms = $query->paginate(20);
        return view('classrooms.index', compact('classrooms'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return redirect()->route('live-lessons.create');
    }

    /**
     * Store a new live classroom.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'scheduled_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled',
        ]);

        $classroom = $this->lessonService->createLesson([
            'school_id' => $this->schoolId(),
            'teacher_id' => Auth::id(),
            'class_id' => $validated['class_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['status'],
            'initial_mode' => Auth::user()->school?->preferred_teaching_mode ?? 'whiteboard',
            'created_by' => Auth::id(),
        ], Auth::user());

        return redirect()->route('classrooms.show', $classroom)
            ->with('success', "Live classroom '{$classroom->title}' created. Room code: {$classroom->room_code}");
    }

    /**
     * Show classroom page (this is the workspace if live).
     */
    public function show(LiveClassroom $classroom)
    {
        $user = Auth::user();
        $session = null;

        if (!$user->isSuperAdmin() && $classroom->school_id !== $this->schoolId()) {
            abort(403);
        }

        if (! $this->lessonService->canAccessLesson($classroom, $user)) {
            abort(403);
        }

        $session = $this->lessonService->ensureSession($classroom, $user);

        $classroom->load(['teacher', 'course', 'classe', 'subject']);

        if ($session) {
            $session->load(['participants.user', 'messages.user', 'whiteboardElements.user']);
        }

        $isTeacher = Auth::id() === $classroom->teacher_id || $user->isSchoolAdmin() || $user->isSchoolOwner();
        $roleInSession = $isTeacher ? 'teacher' : ($user->isParent() ? 'observer' : 'student');
        $roomJoinLink = $classroom->joinUrl();
        // Auto-join participant for current user if session is live/waiting
        $myParticipant = null;
        if ($session && in_array($session->status, ['waiting', 'live'])) {
            $myParticipant = $this->lessonService->addParticipant(
                $session,
                $user,
                $roleInSession,
                $this->realtime->defaultStudentPermissions($classroom, $isTeacher)
            );
        }

        $myPermissions = $session
            ? $this->realtime->participantPermissions($session, $user, $myParticipant)
            : ($roleInSession === 'observer'
                ? [
                    'draw' => false,
                    'type' => false,
                    'chat' => false,
                    'pointer' => false,
                    'code' => false,
                    'download' => false,
                ]
                : $this->realtime->defaultStudentPermissions($classroom, $isTeacher));

        return view('classrooms.workspace', compact(
            'classroom',
            'session',
            'isTeacher',
            'myParticipant',
            'roomJoinLink',
            'myPermissions'
        ));
    }

    /**
     * Start the live session (teacher only).
     */
    public function startSession(LiveClassroom $classroom)
    {
        if (Auth::id() !== $classroom->teacher_id) abort(403);

        $this->lessonService->startSession($classroom, Auth::user());

        return back()->with('success', 'Session is now live!');
    }

    /**
     * End the live session (teacher only).
     */
    public function endSession(LiveClassroom $classroom)
    {
        if (Auth::id() !== $classroom->teacher_id) abort(403);

        $this->lessonService->endSession($classroom, Auth::user());

        return redirect()->route('classrooms.show', $classroom)
            ->with('success', 'Session ended.');
    }

    /**
     * Join via room code (for students).
     */
    public function joinByCode(Request $request)
    {
        return app(LessonJoinController::class)->store($request);
    }

    /**
     * Join room page.
     */
    public function joinForm()
    {
        return redirect()->route('join', array_filter([
            'room_code' => request()->query('room_code') ?? request()->query('join_code'),
        ]));
    }
}
