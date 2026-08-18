<?php

namespace App\Services\Classroom;

use App\Enums\LiveLessonMode;
use App\Events\Classroom\ClassroomEnded;
use App\Events\Classroom\ClassroomModeChanged;
use App\Events\Classroom\ClassroomStarted;
use App\Events\Classroom\ClassroomStatusChanged;
use App\Events\Classroom\ParticipantJoined;
use App\Events\Classroom\ParticipantLeft;
use App\Events\Classroom\StudentPermissionChanged;
use App\Models\Classe;
use App\Models\AttendanceRecord;
use App\Models\ClassroomParticipant;
use App\Models\ClassroomSession;
use App\Models\LiveClassroom;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class LiveClassroomService
{
    public function __construct(protected ClassroomRealtimeService $realtime) {}

    public function createLesson(array $data, User $actor): LiveClassroom
    {
        $schoolId = (int) ($data['school_id'] ?? $actor->school_id);
        abort_unless($schoolId > 0, 422, 'A school or organization is required to create a live lesson.');

        $classroom = LiveClassroom::create([
            'school_id' => $schoolId,
            'course_id' => $data['course_id'] ?? null,
            'class_id' => $data['class_id'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? $actor->id,
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'room_code' => $data['room_code'] ?? LiveClassroom::generateRoomCode(),
            'classroom_mode' => LiveLessonMode::normalize(
                $data['initial_mode']
                    ?? $data['classroom_mode']
                    ?? $data['active_mode']
                    ?? $actor->school?->preferred_teaching_mode
                    ?? 'whiteboard'
            ),
            'status' => $data['status'] ?? 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'settings' => $this->defaultSettings($data['settings'] ?? []),
            'layout_settings' => $data['layout_settings'] ?? [],
            'created_by' => $data['created_by'] ?? $actor->id,
        ]);

        return $classroom->refresh();
    }

    public function ensureSession(LiveClassroom $classroom, User $actor, bool $createIfLive = true): ?ClassroomSession
    {
        $session = $classroom->activeSession();

        if ($session || ! $createIfLive || $classroom->status !== 'live') {
            return $session;
        }

        return ClassroomSession::create([
            'school_id' => $classroom->school_id,
            'live_classroom_id' => $classroom->id,
            'teacher_id' => $classroom->teacher_id,
            'status' => 'waiting',
            'duration_minutes' => $classroom->duration_minutes,
            'active_mode' => LiveLessonMode::normalize($classroom->classroom_mode),
            'mode_settings' => [],
            'metadata' => $this->baseSessionMetadata($classroom),
        ]);
    }

    public function joinLesson(LiveClassroom $classroom, User $user): array
    {
        abort_unless(in_array($classroom->status, ['scheduled', 'live'], true), 422, 'This lesson is not joinable right now.');

        if (! $this->canAccessLesson($classroom, $user)) {
            abort(403);
        }

        $session = $this->ensureJoinSession($classroom);
        $isTeacher = (int) $classroom->teacher_id === (int) $user->id || $user->isSchoolAdmin() || $user->isSchoolOwner();
        $role = $isTeacher ? 'teacher' : ($user->isParent() ? 'observer' : 'student');

        $participant = $this->addParticipant(
            $session,
            $user,
            $role,
            $this->realtime->defaultStudentPermissions($classroom, $isTeacher)
        );

        $attendance = $this->recordAttendanceJoin($classroom, $session, $user, $role);

        return [
            'classroom' => $classroom->refresh(),
            'session' => $session->refresh(),
            'participant' => $participant,
            'attendance' => $attendance,
            'role' => $role,
        ];
    }

    public function startSession(LiveClassroom $classroom, User $actor): ClassroomSession
    {
        $classroom->forceFill([
            'status' => 'live',
            'starts_at' => $classroom->starts_at ?: now(),
            'ends_at' => $classroom->ends_at ?: ($classroom->duration_minutes ? now()->copy()->addMinutes((int) $classroom->duration_minutes) : null),
        ])->save();

        $session = $this->ensureSession($classroom, $actor) ?? ClassroomSession::create([
            'school_id' => $classroom->school_id,
            'live_classroom_id' => $classroom->id,
            'teacher_id' => $classroom->teacher_id,
            'started_at' => now(),
            'status' => 'live',
            'duration_minutes' => $classroom->duration_minutes,
            'active_mode' => LiveLessonMode::normalize($classroom->classroom_mode),
            'mode_settings' => [],
            'metadata' => $this->baseSessionMetadata($classroom),
        ]);

        $session->forceFill([
            'started_at' => $session->started_at ?: now(),
            'ended_at' => null,
            'status' => 'live',
            'duration_minutes' => $session->duration_minutes ?: $classroom->duration_minutes,
            'active_mode' => LiveLessonMode::normalize($session->active_mode ?? $classroom->classroom_mode),
        ])->save();

        $this->addParticipant($session, $actor, 'teacher', $this->realtime->defaultStudentPermissions($classroom, true));

        event(new ClassroomStarted(
            $session->id,
            $classroom->id,
            $classroom->room_code,
            $actor->id,
            $actor->displayName()
        ));

        event(new ClassroomStatusChanged($session->id, 'live', $actor->id, $actor->displayName()));

        return $session->refresh();
    }

    public function endSession(LiveClassroom $classroom, User $actor): ?ClassroomSession
    {
        $session = $classroom->activeSession();

        if ($session) {
            $session->endSession();

            event(new ClassroomEnded(
                $session->id,
                $classroom->id,
                $classroom->room_code,
                $actor->id,
                $actor->displayName()
            ));

            event(new ClassroomStatusChanged($session->id, 'ended', $actor->id, $actor->displayName()));
        }

        $classroom->forceFill([
            'status' => 'ended',
            'ends_at' => now(),
        ])->save();

        return $session?->refresh();
    }

    public function changeMode(ClassroomSession $session, string $mode, User $actor, array $modeSettings = []): ClassroomSession
    {
        $normalized = LiveLessonMode::normalize($mode);

        $session->forceFill([
            'active_mode' => $normalized,
            'mode_settings' => $modeSettings,
        ])->save();

        $classroom = $session->classroom()->first();
        if ($classroom) {
            $classroom->forceFill(['classroom_mode' => $normalized])->save();
        }

        event(new ClassroomModeChanged(
            $session->id,
            $normalized,
            $modeSettings,
            $actor->id,
            $actor->displayName()
        ));

        return $session->refresh();
    }

    public function resolveRoomByCode(string $roomCode): ?LiveClassroom
    {
        $normalized = Str::upper(trim($roomCode));

        return LiveClassroom::query()
            ->whereRaw('UPPER(room_code) = ?', [$normalized])
            ->first();
    }

    public function joinUrl(LiveClassroom $classroom): string
    {
        return $classroom->joinUrl();
    }

    public function addParticipant(ClassroomSession $session, User $user, string $role, ?array $permissions = null): ClassroomParticipant
    {
        $classroom = $session->classroom()->firstOrFail();
        $defaultPermissions = $role === 'observer'
            ? [
                'draw' => false,
                'type' => false,
                'chat' => false,
                'pointer' => false,
                'code' => false,
                'download' => false,
            ]
            : $this->realtime->defaultStudentPermissions($classroom, $role === 'teacher');

        $participant = ClassroomParticipant::firstOrCreate(
            [
                'classroom_session_id' => $session->id,
                'user_id' => $user->id,
            ],
            [
                'school_id' => $session->school_id,
                'role_in_session' => $role,
                'joined_at' => now(),
                'is_active' => true,
                'permissions' => $defaultPermissions,
            ]
        );

        $wasReactivated = false;
        if (! $participant->is_active) {
            $wasReactivated = true;
            $participant->forceFill([
                'is_active' => true,
                'joined_at' => now(),
                'left_at' => null,
                'role_in_session' => $role,
            ])->save();
        }

        $normalized = $this->realtime->normalizePermissions(
            $permissions ?? $participant->permissions ?? $defaultPermissions,
            $classroom,
            $role === 'teacher'
        );

        if ($participant->permissions !== $normalized) {
            $participant->forceFill(['permissions' => $normalized])->save();
        }

        if ($participant->wasRecentlyCreated || $wasReactivated) {
            event(new ParticipantJoined($session->id, $user->id, $user->displayName(), $role));
        }

        return $participant->refresh();
    }

    public function removeParticipant(ClassroomSession $session, User $user): ?ClassroomParticipant
    {
        $participant = $session->participants()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $participant) {
            return null;
        }

        $participant->forceFill([
            'is_active' => false,
            'left_at' => now(),
        ])->save();

        event(new ParticipantLeft($session->id, $user->id, $user->displayName()));

        return $participant->refresh();
    }

    protected function ensureJoinSession(LiveClassroom $classroom): ClassroomSession
    {
        $session = $classroom->activeSession();

        if ($session) {
            return $session;
        }

        return ClassroomSession::create([
            'school_id' => $classroom->school_id,
            'live_classroom_id' => $classroom->id,
            'teacher_id' => $classroom->teacher_id,
            'status' => $classroom->status === 'live' ? 'live' : 'waiting',
            'duration_minutes' => $classroom->duration_minutes,
            'active_mode' => LiveLessonMode::normalize($classroom->classroom_mode),
            'mode_settings' => [],
            'metadata' => $this->baseSessionMetadata($classroom),
            'started_at' => $classroom->status === 'live' ? now() : null,
        ]);
    }

    protected function recordAttendanceJoin(LiveClassroom $classroom, ClassroomSession $session, User $user, string $role): ?AttendanceRecord
    {
        if ($role !== 'student' || ! Schema::hasTable('attendance_records')) {
            return null;
        }

        return AttendanceRecord::updateOrCreate(
            [
                'student_id' => $user->id,
                'attendance_date' => now()->toDateString(),
                'class_id' => $classroom->class_id,
            ],
            [
                'school_id' => $classroom->school_id,
                'live_classroom_id' => $classroom->id,
                'classroom_session_id' => $session->id,
                'teacher_id' => $classroom->teacher_id,
                'status' => 'present',
                'joined_at' => now(),
                'left_at' => null,
                'duration_minutes' => null,
                'notes' => 'Joined live lesson via room code.',
            ]
        );
    }

    public function savePermissions(ClassroomSession $session, array $permissions, User $actor, ?int $participantId = null): array
    {
        $result = $this->realtime->updateStudentPermissions($session, $permissions, $actor, $participantId);

        event(new StudentPermissionChanged(
            $session->id,
            $result['permissions'],
            $actor->id,
            $actor->displayName(),
            $participantId,
            $participantId ? 'participant' : 'room'
        ));

        return $result;
    }

    public function saveSessionState(ClassroomSession $session, User $actor, ?string $sessionNotes = null, ?array $resources = null, ?array $whiteboardState = null): array
    {
        return $this->realtime->saveSessionSnapshot($session, $actor, $sessionNotes, $resources, $whiteboardState);
    }

    public function normalizeMode(?string $mode): string
    {
        return LiveLessonMode::normalize($mode);
    }

    public function lessonAudience(LiveClassroom $classroom): array
    {
        $settings = (array) ($classroom->settings ?? []);
        $audience = (array) data_get($settings, 'lesson_audience', []);

        return [
            'mode' => data_get($audience, 'mode', $classroom->class_id ? 'group' : 'learner'),
            'class_id' => data_get($audience, 'class_id', $classroom->class_id),
            'learner_ids' => array_values(array_filter(array_map(
                static fn ($id) => (int) $id,
                (array) data_get($audience, 'learner_ids', [])
            ))),
        ];
    }

    public function canAccessLesson(LiveClassroom $classroom, User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ((int) $classroom->teacher_id === (int) $user->id) {
            return true;
        }

        if (! $user->school_id || (int) $classroom->school_id !== (int) $user->school_id) {
            return false;
        }

        if ($user->isSchoolOwner() || $user->isSchoolAdmin()) {
            return true;
        }

        $activeSession = $classroom->activeSession();
        if ($activeSession && $activeSession->participants()->where('user_id', $user->id)->where('is_active', true)->exists()) {
            return true;
        }

        $audience = $this->lessonAudience($classroom);

        if ($user->isStudent()) {
            if (! empty($audience['class_id'])) {
                $inClass = Classe::whereKey($audience['class_id'])
                    ->whereHas('students', fn ($query) => $query->whereKey($user->id))
                    ->exists();

                if ($inClass) {
                    return true;
                }
            }

            return in_array((int) $user->id, $audience['learner_ids'], true);
        }

        if ($user->isParent()) {
            $childIds = $user->children()->pluck('users.id')->map(fn ($id) => (int) $id)->all();

            if (! empty($audience['class_id'])) {
                $inClass = Classe::whereKey($audience['class_id'])
                    ->whereHas('students', fn ($query) => $query->whereKey($childIds))
                    ->exists();

                if ($inClass) {
                    return true;
                }
            }

            return ! empty(array_intersect($childIds, $audience['learner_ids']));
        }

        return false;
    }

    protected function baseSessionMetadata(LiveClassroom $classroom): array
    {
        return [
            'join_code' => $classroom->room_code,
            'join_link' => $classroom->joinUrl(),
            'whiteboard_state' => $this->defaultWhiteboardState(),
            'student_permissions' => $this->realtime->defaultStudentPermissions($classroom, false),
            'code_language' => 'plaintext',
            'code_active_file_key' => 'html',
            'code_workspace' => [
                'active_file_key' => 'html',
                'files' => [
                    'html' => [
                        'filename' => 'index.html',
                        'language' => 'html',
                        'content' => '',
                        'label' => 'HTML',
                    ],
                    'css' => [
                        'filename' => 'styles.css',
                        'language' => 'css',
                        'content' => '',
                        'label' => 'CSS',
                    ],
                    'js' => [
                        'filename' => 'script.js',
                        'language' => 'javascript',
                        'content' => '',
                        'label' => 'JavaScript',
                    ],
                ],
            ],
            'resources' => [],
            'session_notes' => '',
        ];
    }

    protected function defaultWhiteboardState(): array
    {
        return [
            'active_page' => 'page-1',
            'zoom' => 100,
            'viewport' => ['x' => 0, 'y' => 0],
            'settings' => [
                'board_locked' => false,
                'follow_teacher_page' => true,
                'follow_teacher_viewport' => false,
                'presentation_mode' => false,
                'allow_learner_page_switch' => true,
                'allow_learner_page_create' => false,
                'allow_learner_object_move' => true,
                'allow_learner_draw' => true,
                'allow_learner_erase' => false,
                'allow_learner_comments' => true,
                'allow_learner_images' => false,
            ],
            'pages' => [
                [
                    'key' => 'page-1',
                    'title' => 'Page 1',
                    'page_number' => 1,
                    'background_type' => 'plain_white',
                    'background_value' => '#ffffff',
                    'thumbnail_path' => null,
                    'is_locked' => false,
                    'settings' => [],
                ],
            ],
        ];
    }

    protected function defaultSettings(array $settings): array
    {
        return array_replace([
            'allow_student_draw' => true,
            'allow_student_type' => true,
            'allow_student_chat' => true,
            'show_pointer' => true,
            'allow_student_code' => true,
            'allow_resource_download' => false,
        ], Arr::only($settings, [
            'allow_student_draw',
            'allow_student_type',
            'allow_student_chat',
            'show_pointer',
            'allow_student_code',
            'allow_resource_download',
            'lesson_audience',
        ]));
    }
}
