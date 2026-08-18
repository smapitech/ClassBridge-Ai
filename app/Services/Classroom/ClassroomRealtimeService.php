<?php

namespace App\Services\Classroom;

use App\Models\ClassroomParticipant;
use App\Models\ClassroomSession;
use App\Models\LiveClassroom;
use App\Models\User;

class ClassroomRealtimeService
{
    public function __construct(protected WhiteboardService $whiteboardService)
    {
    }

    public function defaultStudentPermissions(LiveClassroom $classroom, bool $isTeacher = false): array
    {
        $defaults = [
            'draw' => $isTeacher ? true : (bool) data_get($classroom->settings, 'allow_student_draw', true),
            'type' => $isTeacher ? true : (bool) data_get($classroom->settings, 'allow_student_type', true),
            'chat' => $isTeacher ? true : (bool) data_get($classroom->settings, 'allow_student_chat', true),
            'pointer' => $isTeacher ? true : (bool) data_get($classroom->settings, 'show_pointer', true),
            'code' => $isTeacher ? true : (bool) data_get($classroom->settings, 'allow_student_code', true),
            'download' => $isTeacher ? true : (bool) data_get($classroom->settings, 'allow_resource_download', false),
        ];

        return array_merge($defaults, [
            'whiteboard_draw' => $defaults['draw'],
            'whiteboard_text' => $defaults['type'],
            'whiteboard_shapes' => $defaults['draw'],
            'whiteboard_images' => $isTeacher,
            'whiteboard_erase' => $defaults['draw'],
            'whiteboard_pointer' => $defaults['pointer'],
            'whiteboard_comments' => $defaults['chat'],
            'whiteboard_page_switch' => true,
            'whiteboard_page_create' => $isTeacher,
            'whiteboard_object_move' => $defaults['draw'],
            'whiteboard_download' => $defaults['download'],
            'whiteboard_lock_board' => $isTeacher,
            'whiteboard_follow_teacher_page' => true,
            'whiteboard_follow_teacher_viewport' => false,
        ]);
    }

    public function normalizePermissions(?array $permissions, LiveClassroom $classroom, bool $isTeacher = false): array
    {
        return array_merge(
            $this->defaultStudentPermissions($classroom, $isTeacher),
            $permissions ?? []
        );
    }

    public function participantPermissions(ClassroomSession $session, User $user, ?ClassroomParticipant $participant = null): array
    {
        $classroom = $session->relationLoaded('classroom') ? $session->classroom : $session->classroom()->first();
        $participant ??= $session->participants()->where('user_id', $user->id)->latest('joined_at')->first();

        $isTeacher = $this->isTeacher($session, $user, $participant, $classroom);

        return $this->normalizePermissions(
            $participant?->permissions,
            $classroom ?? $session->classroom()->firstOrFail(),
            $isTeacher
        );
    }

    public function isTeacher(ClassroomSession $session, User $user, ?ClassroomParticipant $participant = null, ?LiveClassroom $classroom = null): bool
    {
        $classroom ??= $session->relationLoaded('classroom') ? $session->classroom : $session->classroom()->first();

        return $user->isSuperAdmin()
            || $user->isSchoolAdmin()
            || $user->isSchoolOwner()
            || ($classroom && (int) $classroom->teacher_id === (int) $user->id)
            || ($participant && $participant->role_in_session === 'teacher');
    }

    public function workspaceSnapshot(ClassroomSession $session): array
    {
        $classroom = $session->relationLoaded('classroom') ? $session->classroom : $session->classroom()->first();
        $metadata = (array) ($session->metadata ?? []);

        return [
            'join_code' => $classroom?->room_code,
            'join_link' => $classroom?->joinUrl(),
            'whiteboard_state' => $this->whiteboardService->workspaceState($session),
            'code_draft' => (string) data_get($metadata, 'code_draft', ''),
            'code_language' => (string) data_get($metadata, 'code_language', 'plaintext'),
            'code_active_file_key' => (string) data_get($metadata, 'code_active_file_key', 'html'),
            'code_workspace' => data_get($metadata, 'code_workspace', []),
            'code_tabs' => data_get($metadata, 'code_tabs', []),
            'saved_at' => data_get($metadata, 'saved_at'),
            'student_permissions' => data_get($metadata, 'student_permissions', []),
            'resources' => data_get($metadata, 'resources', []),
            'session_notes' => data_get($metadata, 'session_notes', ''),
            'textpad_comments' => data_get($metadata, 'textpad_comments', []),
            'mode' => $session->active_mode ?? $classroom?->classroom_mode ?? 'whiteboard',
        ];
    }

    public function storeCodeDraft(ClassroomSession $session, string $code, User $actor, ?string $language = null, ?array $files = null, ?string $activeFileKey = null): array
    {
        // TODO: move classroom draft history to a dedicated persistence table when the sync layer is split out.
        $metadata = (array) ($session->metadata ?? []);
        $workspace = $this->normalizeCodeWorkspace($metadata, $code, $language, $files, $activeFileKey);

        $metadata['code_workspace'] = $workspace;
        $metadata['code_tabs'] = $workspace['files'];
        $metadata['code_active_file_key'] = $workspace['active_file_key'];
        $metadata['code_draft'] = $workspace['active_code'];
        $metadata['code_language'] = $workspace['active_language'];
        $metadata['code_updated_at'] = now()->toIso8601String();
        $metadata['code_updated_by'] = [
            'user_id' => $actor->id,
            'user_name' => $actor->displayName(),
        ];

        $session->metadata = $metadata;
        $session->save();

        return $metadata;
    }

    public function saveSessionSnapshot(ClassroomSession $session, User $actor, ?string $sessionNotes = null, ?array $resources = null, ?array $whiteboardState = null): array
    {
        // TODO: persist full session replays separately once the classroom event log is introduced.
        $metadata = (array) ($session->metadata ?? []);

        if ($whiteboardState !== null) {
            $whiteboardState = $this->whiteboardService->normalizeState($whiteboardState);
            $this->whiteboardService->syncFromState($session, $whiteboardState, $actor);
            $metadata = (array) ($session->metadata ?? $metadata);
        }

        if ($sessionNotes !== null) {
            $metadata['session_notes'] = $sessionNotes;
        }

        if ($resources !== null) {
            $metadata['resources'] = $resources;
        }

        if (! isset($metadata['whiteboard_state']) || ! is_array($metadata['whiteboard_state'])) {
            $metadata['whiteboard_state'] = $this->whiteboardService->defaultState();
        }

        $whiteboardSnapshot = $session->whiteboardElements()
            ->with('user')
            ->latest()
            ->get()
            ->map(static function ($element) {
                return [
                    'id' => $element->id,
                    'user_id' => $element->user_id,
                    'user_name' => $element->user?->displayName(),
                    'element_type' => $element->element_type,
                    'data' => $element->data,
                    'created_at' => $element->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        $metadata['saved_at'] = now()->toIso8601String();
        $metadata['saved_by'] = [
            'user_id' => $actor->id,
            'user_name' => $actor->displayName(),
        ];
        $metadata['last_snapshot'] = [
            'whiteboard_count' => count($whiteboardSnapshot),
            'textpad_length' => strlen((string) ($session->textpad_snapshot ?? '')),
            'code_length' => strlen((string) data_get($metadata, 'code_draft', '')),
            'resources_count' => count((array) data_get($metadata, 'resources', [])),
            'session_notes_length' => strlen((string) data_get($metadata, 'session_notes', '')),
            'mode' => $session->active_mode ?? 'whiteboard',
        ];

        $session->whiteboard_snapshot = $whiteboardSnapshot;
        $session->metadata = $metadata;
        $session->save();

        return [
            'session' => $session,
            'metadata' => $metadata,
            'whiteboard_snapshot' => $whiteboardSnapshot,
        ];
    }

    protected function normalizeCodeWorkspace(array $metadata, string $code, ?string $language = null, ?array $files = null, ?string $activeFileKey = null): array
    {
        $activeFileKey = $this->normalizeWorkspaceFileKey($activeFileKey ?: data_get($metadata, 'code_active_file_key', 'html'));

        $defaults = [
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
        ];

        $existing = (array) data_get($metadata, 'code_workspace.files', []);
        $incoming = is_array($files) ? $files : [];

        $workspaceFiles = [];
        foreach ($defaults as $key => $fallback) {
            $workspaceFiles[$key] = $this->normalizeWorkspaceFile($incoming[$key] ?? $existing[$key] ?? null, $fallback);
        }

        foreach (array_merge(array_keys($incoming), array_keys($existing)) as $key) {
            $normalizedKey = $this->normalizeWorkspaceFileKey($key);

            if (array_key_exists($normalizedKey, $workspaceFiles)) {
                continue;
            }

            $candidate = $incoming[$key] ?? $existing[$key] ?? null;
            $workspaceFiles[$normalizedKey] = $this->normalizeWorkspaceFile(
                $candidate,
                $this->workspaceFileFallback($normalizedKey, $candidate)
            );
        }

        if (! array_key_exists($activeFileKey, $workspaceFiles)) {
            $activeFileKey = array_key_first($workspaceFiles) ?: 'html';
        }

        if (trim($code) !== '') {
            $workspaceFiles[$activeFileKey]['content'] = $code;
        }

        $activeFile = $workspaceFiles[$activeFileKey];

        return [
            'active_file_key' => $activeFileKey,
            'active_language' => $language ?: ($activeFile['language'] ?? 'plaintext'),
            'active_code' => (string) ($activeFile['content'] ?? $code),
            'files' => $workspaceFiles,
        ];
    }

    protected function normalizeWorkspaceFileKey(?string $key): string
    {
        $value = strtolower(trim((string) $key));
        $value = preg_replace('/\s+/', '-', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9._-]/', '', $value) ?? $value;
        $value = trim(preg_replace('/-+/', '-', $value) ?? $value, '-');

        return $value !== '' ? $value : 'html';
    }

    protected function normalizeWorkspaceFile(mixed $candidate, array $fallback): array
    {
        $candidate = is_array($candidate) ? $candidate : [];

        return [
            'filename' => (string) ($candidate['filename'] ?? $fallback['filename']),
            'language' => (string) ($candidate['language'] ?? $fallback['language']),
            'content' => (string) ($candidate['content'] ?? $fallback['content']),
            'label' => (string) ($candidate['label'] ?? $fallback['label']),
            'sort_order' => (int) ($candidate['sort_order'] ?? $fallback['sort_order'] ?? 0),
        ];
    }

    protected function workspaceFileFallback(string $key, mixed $candidate = null): array
    {
        $candidate = is_array($candidate) ? $candidate : [];
        $filename = (string) ($candidate['filename'] ?? '');
        $language = (string) ($candidate['language'] ?? '');
        $filename = $filename !== '' ? $filename : (str_contains($key, '.') ? $key : "{$key}.js");
        $language = $language !== '' ? $language : $this->workspaceLanguageFromFilename($filename);

        return [
            'filename' => $filename,
            'language' => $language,
            'content' => '',
            'label' => $this->workspaceLabelFromLanguage($language),
            'sort_order' => (int) ($candidate['sort_order'] ?? 0),
        ];
    }

    protected function workspaceLanguageFromFilename(string $filename): string
    {
        $lower = strtolower(trim($filename));

        if (str_ends_with($lower, '.html') || str_ends_with($lower, '.htm')) {
            return 'html';
        }

        if (str_ends_with($lower, '.css')) {
            return 'css';
        }

        if (str_ends_with($lower, '.php')) {
            return 'php';
        }

        return 'javascript';
    }

    protected function workspaceLabelFromLanguage(string $language): string
    {
        $value = strtolower(trim($language));

        return match ($value) {
            'html' => 'HTML',
            'css' => 'CSS',
            'javascript', 'js' => 'JavaScript',
            'php' => 'PHP',
            default => $value !== '' ? strtoupper($value) : 'TEXT',
        };
    }

    public function updateStudentPermissions(ClassroomSession $session, array $permissions, User $actor, ?int $participantId = null): array
    {
        $normalized = $this->normalizePermissions($permissions, $session->classroom()->firstOrFail(), false);

        $metadata = (array) ($session->metadata ?? []);
        $metadata['student_permissions'] = $normalized;
        $metadata['permissions_updated_at'] = now()->toIso8601String();
        $metadata['permissions_updated_by'] = [
            'user_id' => $actor->id,
            'user_name' => $actor->displayName(),
        ];

        $session->metadata = $metadata;
        $session->save();

        $participants = $session->participants()
            ->where('role_in_session', 'student')
            ->where('is_active', true)
            ->when($participantId, fn ($query) => $query->whereKey($participantId))
            ->get();

        foreach ($participants as $participant) {
            $participant->permissions = array_merge($participant->permissions ?? [], $normalized);
            $participant->save();
        }

        return [
            'permissions' => $normalized,
            'participants' => $participants,
            'metadata' => $metadata,
        ];
    }

    protected function normalizeWhiteboardState(array $whiteboardState): array
    {
        return $this->whiteboardService->normalizeState($whiteboardState);
    }
}
