<?php

namespace App\Http\Controllers\Classroom;

use App\Events\Classroom\ChatMessageSent;
use App\Events\Classroom\CodeSaved;
use App\Events\Classroom\CodeUpdated;
use App\Events\Classroom\PointerMoved;
use App\Events\Classroom\TextPadUpdated;
use App\Events\Classroom\WhiteboardElementDeleted;
use App\Events\Classroom\WhiteboardCleared;
use App\Events\Classroom\WhiteboardElementCreated;
use App\Events\Classroom\WhiteboardElementUpdated;
use App\Http\Controllers\Controller;
use App\Models\ClassroomMessage;
use App\Models\ClassroomParticipant;
use App\Models\ClassroomSession;
use App\Models\PointerEvent;
use App\Models\WhiteboardElement;
use App\Models\WhiteboardSnapshot;
use App\Services\Classroom\LiveClassroomService;
use App\Services\Classroom\ClassroomRealtimeService;
use App\Services\Classroom\WhiteboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassroomApiController extends Controller
{
    public function __construct(
        protected ClassroomRealtimeService $realtime,
        protected LiveClassroomService $lessonService,
        protected WhiteboardService $whiteboards,
    ) {}

    protected function sessionOrFail($sessionId): ClassroomSession
    {
        $session = ClassroomSession::findOrFail($sessionId);

        if (!Auth::user()->isSuperAdmin() && $session->school_id !== Auth::user()->school_id) {
            abort(403);
        }

        return $session;
    }

    protected function currentParticipant(ClassroomSession $session): ?ClassroomParticipant
    {
        return $session->participants()
            ->where('user_id', Auth::id())
            ->latest('joined_at')
            ->first();
    }

    protected function currentPermissions(ClassroomSession $session): array
    {
        return $this->realtime->participantPermissions($session, Auth::user(), $this->currentParticipant($session));
    }

    protected function ensurePermission(ClassroomSession $session, string $key): void
    {
        if (($this->currentPermissions($session)[$key] ?? false) !== true) {
            abort(403);
        }
    }

    protected function ensureTeacher(ClassroomSession $session): void
    {
        if (!$this->realtime->isTeacher($session, Auth::user(), $this->currentParticipant($session))) {
            abort(403);
        }
    }

    // ========== WHITEBOARD ==========
    public function saveWhiteboardElement(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:whiteboard_elements,id',
            'action' => 'nullable|string|in:upsert,delete',
            'element_type' => 'nullable|string|max:80',
            'data' => 'nullable|array',
        ]);

        $action = $validated['action'] ?? 'upsert';
        $elementType = strtolower((string) ($validated['element_type'] ?? 'whiteboard_object'));
        $data = array_merge([
            'page_key' => data_get($session->metadata, 'whiteboard_state.active_page', 'page-1'),
        ], (array) ($validated['data'] ?? []));

        if (in_array($action, ['upsert', 'delete'], true)) {
            $this->ensureWhiteboardObjectPermission($session, $elementType, $action);
        }

        if ($action === 'delete') {
            abort_unless(! empty($validated['id']), 422, 'A whiteboard element id is required to delete it.');

            $element = WhiteboardElement::where('classroom_session_id', $session->id)->findOrFail($validated['id']);
            $deletedElementId = $element->id;
            $deletedElementType = $element->element_type;
            $deletedPageKey = data_get($element->data, 'page_key');

            $element->delete();

            event(new WhiteboardElementDeleted(
                $session->id,
                $deletedElementId,
                Auth::id(),
                Auth::user()->displayName(),
                $deletedElementType,
                is_string($deletedPageKey) ? $deletedPageKey : null,
            ));

            return response()->json(['success' => true, 'deleted_id' => $deletedElementId]);
        }

        $board = $this->whiteboards->ensureWhiteboard($session, Auth::user());
        $page = $board->pages()->where('page_key', (string) data_get($data, 'page_key', $board->currentPage?->page_key ?? 'page-1'))->first()
            ?? $board->currentPage
            ?? $board->pages()->orderBy('page_number')->first();
        $elementUuid = data_get($data, 'element_uuid') ?: (string) \Illuminate\Support\Str::uuid();
        $data['element_uuid'] = $elementUuid;
        $data['page_key'] = $page?->page_key ?? data_get($data, 'page_key', 'page-1');

        if (!empty($validated['id'])) {
            $element = WhiteboardElement::where('classroom_session_id', $session->id)->findOrFail($validated['id']);
            $element->update([
                'whiteboard_id' => $board->id,
                'whiteboard_page_id' => $page?->id,
                'element_uuid' => $element->element_uuid ?: $elementUuid,
                'updated_by' => Auth::id(),
                'z_index' => (int) data_get($data, 'z_index', $element->z_index ?? 0),
                'is_locked' => (bool) data_get($data, 'is_locked', $element->is_locked ?? false),
                'element_type' => $validated['element_type'] ?? $element->element_type,
                'data' => $data,
            ]);
            $element->load('user');

            event(new WhiteboardElementUpdated($element));

            return response()->json(['success' => true, 'element' => $element]);
        }

        $element = WhiteboardElement::create([
            'school_id' => $session->school_id,
            'classroom_session_id' => $session->id,
            'whiteboard_id' => $board->id,
            'whiteboard_page_id' => $page?->id,
            'element_uuid' => $elementUuid,
            'user_id' => Auth::id(),
            'updated_by' => Auth::id(),
            'element_type' => $validated['element_type'] ?? 'whiteboard_object',
            'z_index' => (int) data_get($data, 'z_index', 0),
            'is_locked' => (bool) data_get($data, 'is_locked', false),
            'data' => $data,
        ])->load('user');

        event(new WhiteboardElementCreated($element));

        return response()->json(['success' => true, 'element' => $element]);
    }

    public function fetchWhiteboard($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $board = $this->whiteboards->ensureWhiteboard($session, Auth::user());

        return response()->json([
            'whiteboard' => $board->load(['pages' => fn ($query) => $query->orderBy('page_number'), 'currentPage', 'snapshots' => fn ($query) => $query->latest('id')->limit(20)]),
            'whiteboard_state' => $this->whiteboards->workspaceState($session),
            'elements' => $session->whiteboardElements()->with('user')->latest()->get(),
        ]);
    }

    public function clearWhiteboard($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $pageKey = request()->input('page_key');
        if (! $pageKey) {
            $pageKey = data_get($session->metadata, 'whiteboard_state.active_page');
        }
        if (is_string($pageKey) && $pageKey !== '') {
            $this->whiteboards->createSnapshot($session, Auth::user(), 'Before clear', 'before_clear', $pageKey);
        }
        $query = $session->whiteboardElements();
        if (is_string($pageKey) && $pageKey !== '') {
            $query->where('data->page_key', $pageKey);
        } else {
            $pageKey = null;
        }

        $deletedCount = (clone $query)->count();
        $query->delete();
        event(new WhiteboardCleared($session->id, Auth::id(), Auth::user()->displayName(), $pageKey, $deletedCount));

        return response()->json([
            'success' => true,
            'page_key' => $pageKey,
            'deleted_count' => $deletedCount,
        ]);
    }

    public function listWhiteboardSnapshots($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $board = $this->whiteboards->ensureWhiteboard($session, Auth::user());

        return response()->json([
            'snapshots' => $board->snapshots()
                ->with(['creator', 'page'])
                ->latest('id')
                ->limit(30)
                ->get(),
        ]);
    }

    public function createWhiteboardSnapshot(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'reason' => 'nullable|string|max:120',
            'page_key' => 'nullable|string|max:80',
        ]);

        $snapshot = $this->whiteboards->createSnapshot(
            $session,
            Auth::user(),
            $validated['name'] ?? null,
            $validated['reason'] ?? null,
            $validated['page_key'] ?? null
        );

        return response()->json([
            'success' => true,
            'snapshot' => $snapshot,
        ]);
    }

    public function restoreWhiteboardSnapshot($sessionId, WhiteboardSnapshot $snapshot)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);
        abort_unless((int) $snapshot->whiteboard_id === (int) optional($session->whiteboard)->id, 404);

        $board = $this->whiteboards->restoreSnapshot($session, $snapshot, Auth::user());

        return response()->json([
            'success' => true,
            'whiteboard_state' => $this->whiteboards->workspaceState($session),
            'whiteboard' => $board->load(['pages' => fn ($query) => $query->orderBy('page_number'), 'currentPage']),
        ]);
    }

    // ========== TEXT PAD ==========
    public function saveTextPad(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'type');

        $request->validate([
            'content' => 'nullable|string',
            'comments' => 'nullable|array|max:50',
            'comments.*.id' => 'nullable|string|max:80',
            'comments.*.message' => 'nullable|string|max:2000',
            'comments.*.author_name' => 'nullable|string|max:120',
            'comments.*.created_at' => 'nullable|string|max:80',
            'comments.*.user_id' => 'nullable|integer',
        ]);

        $session->textpad_snapshot = $request->input('content', '');
        $metadata = (array) ($session->metadata ?? []);
        $metadata['textpad_comments'] = $this->normalizeTextPadComments(
            $request->input('comments', data_get($metadata, 'textpad_comments', []))
        );
        $session->metadata = $metadata;
        $session->save();

        event(new TextPadUpdated(
            $session->id,
            (string) $session->textpad_snapshot,
            Auth::id(),
            Auth::user()->displayName(),
            $metadata['textpad_comments']
        ));

        return response()->json([
            'success' => true,
            'content' => $session->textpad_snapshot,
            'comments' => $metadata['textpad_comments'],
        ]);
    }

    public function fetchTextPad($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);

        return response()->json([
            'content' => $session->textpad_snapshot ?? '',
            'comments' => $this->normalizeTextPadComments(data_get($session->metadata, 'textpad_comments', [])),
        ]);
    }

    // ========== CODE EDITOR ==========
    public function fetchCode($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $workspace = (array) data_get($session->metadata, 'code_workspace', []);

        return response()->json([
            'code' => data_get($session->metadata, 'code_draft', ''),
            'language' => data_get($session->metadata, 'code_language', 'plaintext'),
            'saved_at' => data_get($session->metadata, 'saved_at') ?? data_get($session->metadata, 'code_updated_at'),
            'active_file_key' => data_get($session->metadata, 'code_active_file_key', data_get($workspace, 'active_file_key', 'html')),
            'files' => data_get($workspace, 'files', data_get($session->metadata, 'code_tabs', [])),
            'workspace' => $workspace,
        ]);
    }

    public function saveCode(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'code');

        $validated = $request->validate([
            'code' => 'nullable|string|max:50000',
            'language' => 'nullable|string|max:40',
            'active_file_key' => 'nullable|string|max:50',
            'code_tabs' => 'nullable|array',
            'code_tabs.*' => 'nullable|array',
            'persist' => 'nullable|boolean',
        ]);

        $tabs = $validated['code_tabs'] ?? null;
        $activeFileKey = $validated['active_file_key'] ?? data_get($session->metadata, 'code_active_file_key', 'html');
        $code = (string) ($validated['code'] ?? data_get($tabs, $activeFileKey . '.content', ''));
        $language = $validated['language'] ?? data_get($session->metadata, 'code_language', 'plaintext');
        $metadata = $this->realtime->storeCodeDraft($session, $code, Auth::user(), $language, $tabs, $activeFileKey);
        $workspace = (array) data_get($metadata, 'code_workspace', []);

        event(new CodeUpdated(
            $session->id,
            $code,
            Auth::id(),
            Auth::user()->displayName(),
            $language,
            data_get($workspace, 'files', []),
            data_get($workspace, 'active_file_key', $activeFileKey)
        ));

        if ($request->boolean('persist')) {
            event(new CodeSaved(
                $session->id,
                $code,
                Auth::id(),
                Auth::user()->displayName(),
                data_get($metadata, 'code_updated_at'),
                $language,
                data_get($workspace, 'files', []),
                data_get($workspace, 'active_file_key', $activeFileKey)
            ));
        }

        return response()->json([
            'success' => true,
            'code' => $code,
            'language' => $language,
            'saved_at' => data_get($metadata, 'code_updated_at'),
            'active_file_key' => data_get($workspace, 'active_file_key', $activeFileKey),
            'files' => data_get($workspace, 'files', []),
        ]);
    }

    public function saveSession(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'session_notes' => 'nullable|string|max:20000',
            'resources' => 'nullable|array',
            'whiteboard_state' => 'nullable|array',
        ]);

        $snapshot = $this->lessonService->saveSessionState(
            $session,
            Auth::user(),
            $validated['session_notes'] ?? null,
            $validated['resources'] ?? null,
            $validated['whiteboard_state'] ?? null
        );
        $code = (string) data_get($snapshot['metadata'], 'code_draft', '');
        $language = (string) data_get($snapshot['metadata'], 'code_language', 'plaintext');
        $workspace = (array) data_get($snapshot['metadata'], 'code_workspace', []);

        event(new CodeSaved(
            $session->id,
            $code,
            Auth::id(),
            Auth::user()->displayName(),
            data_get($snapshot['metadata'], 'saved_at'),
            $language,
            data_get($workspace, 'files', []),
            data_get($workspace, 'active_file_key', data_get($snapshot['metadata'], 'code_active_file_key', 'html'))
        ));

        return response()->json([
            'success' => true,
            'saved_at' => data_get($snapshot['metadata'], 'saved_at'),
            'whiteboard_count' => count($snapshot['whiteboard_snapshot'] ?? []),
            'code_length' => strlen($code),
            'session_notes' => data_get($snapshot['metadata'], 'session_notes', ''),
            'resources' => data_get($snapshot['metadata'], 'resources', []),
        ]);
    }

    // ========== CHAT ==========
    public function sendMessage(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'chat');

        $request->validate(['message' => 'required|string|max:2000']);

        $message = ClassroomMessage::create([
            'school_id' => $session->school_id,
            'classroom_session_id' => $session->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'message_type' => 'text',
        ]);
        $message->load('user');

        event(new ChatMessageSent($message));

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function fetchMessages(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $query = $session->messages()->with('user')->latest();

        if ($request->since_id) {
            $query->where('id', '>', $request->since_id);
        }

        return response()->json(['messages' => $query->take(50)->get()->reverse()->values()]);
    }

    // ========== POINTER ==========
    public function updatePointer(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'pointer');

        $request->validate([
            'x_position' => 'required|numeric',
            'y_position' => 'required|numeric',
        ]);

        $pointer = PointerEvent::create([
            'school_id' => $session->school_id,
            'classroom_session_id' => $session->id,
            'user_id' => Auth::id(),
            'x_position' => $request->x_position,
            'y_position' => $request->y_position,
            'target_area' => $request->target_area ?? 'whiteboard',
            'created_at' => now(),
        ]);

        event(new PointerMoved($pointer->load('user')));

        return response()->json(['success' => true]);
    }

    public function fetchPointers($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);

        return response()->json([
            'pointers' => PointerEvent::where('classroom_session_id', $session->id)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->where('user_id', '!=', Auth::id())
                ->with('user')
                ->latest('created_at')
                ->get()
                ->unique('user_id')
                ->values(),
        ]);
    }

    // ========== PARTICIPANTS ==========
    public function fetchParticipants($sessionId)
    {
        return response()->json([
            'participants' => $this->sessionOrFail($sessionId)->activeParticipants()->with('user')->get(),
        ]);
    }

    public function leave($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->lessonService->removeParticipant($session, Auth::user());

        return response()->json(['success' => true]);
    }

    public function updatePermissions(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.draw' => 'sometimes|boolean',
            'permissions.type' => 'sometimes|boolean',
            'permissions.chat' => 'sometimes|boolean',
            'permissions.pointer' => 'sometimes|boolean',
            'permissions.code' => 'sometimes|boolean',
            'permissions.download' => 'sometimes|boolean',
            'permissions.whiteboard_draw' => 'sometimes|boolean',
            'permissions.whiteboard_text' => 'sometimes|boolean',
            'permissions.whiteboard_shapes' => 'sometimes|boolean',
            'permissions.whiteboard_images' => 'sometimes|boolean',
            'permissions.whiteboard_erase' => 'sometimes|boolean',
            'permissions.whiteboard_pointer' => 'sometimes|boolean',
            'permissions.whiteboard_comments' => 'sometimes|boolean',
            'permissions.whiteboard_page_switch' => 'sometimes|boolean',
            'permissions.whiteboard_page_create' => 'sometimes|boolean',
            'permissions.whiteboard_object_move' => 'sometimes|boolean',
            'permissions.whiteboard_download' => 'sometimes|boolean',
            'participant_id' => 'nullable|integer|exists:classroom_participants,id',
        ]);

        $result = $this->lessonService->savePermissions(
            $session,
            $validated['permissions'],
            Auth::user(),
            $validated['participant_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'permissions' => $result['permissions'],
            'metadata' => $result['metadata'],
        ]);
    }

    // ========== CLASSROOM MODE ==========
    /** POST /api/classroom/{session}/mode */
    public function updateMode(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $request->validate([
            'mode' => ['required', Rule::in(\App\Enums\LiveLessonMode::acceptedValues())],
            'mode_settings' => ['nullable', 'array'],
        ]);

        $updated = $this->lessonService->changeMode(
            $session,
            $request->string('mode')->toString(),
            Auth::user(),
            $request->input('mode_settings', [])
        );

        return response()->json(['success' => true, 'mode' => $updated->active_mode]);
    }

    /** GET /api/classroom/{session}/state */
    public function sessionState($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);

        return response()->json(array_merge([
            'status' => $session->status,
            'mode' => $session->active_mode ?? 'whiteboard',
            'mode_settings' => $session->mode_settings ?? [],
            'started_at' => $session->started_at,
            'ended_at' => $session->ended_at,
        ], $this->realtime->workspaceSnapshot($session)));
    }

    protected function normalizeTextPadComments(array $comments): array
    {
        return array_values(array_filter(array_map(static function ($comment, $index) {
            if (! is_array($comment)) {
                return null;
            }

            $message = trim((string) ($comment['message'] ?? ''));
            if ($message === '') {
                return null;
            }

            return [
                'id' => trim((string) ($comment['id'] ?? ('comment-' . ($index + 1)))),
                'message' => $message,
                'author_name' => trim((string) ($comment['author_name'] ?? 'Teacher')),
                'created_at' => trim((string) ($comment['created_at'] ?? now()->toIso8601String())),
                'user_id' => isset($comment['user_id']) ? (int) $comment['user_id'] : null,
            ];
        }, $comments, array_keys($comments))));
    }

    protected function ensureWhiteboardObjectPermission(ClassroomSession $session, string $elementType, string $action): void
    {
        if ($this->realtime->isTeacher($session, Auth::user(), $this->currentParticipant($session))) {
            return;
        }

        $permissions = $this->currentPermissions($session);
        $type = strtolower($elementType);

        $requiresText = in_array($type, ['text', 'i-text', 'textbox', 'equation', 'comment'], true);
        $requiresImage = $type === 'image';
        $requiresDraw = ! $requiresText && ! $requiresImage;

        if ($action === 'delete') {
            abort_unless((bool) ($permissions['whiteboard_erase'] ?? $permissions['draw'] ?? false), 403);
            return;
        }

        if ($requiresImage) {
            abort_unless((bool) ($permissions['whiteboard_images'] ?? false), 403);
            return;
        }

        if ($requiresText) {
            abort_unless((bool) ($permissions['whiteboard_text'] ?? $permissions['type'] ?? false), 403);
            return;
        }

        if ($requiresDraw) {
            abort_unless((bool) ($permissions['whiteboard_draw'] ?? $permissions['draw'] ?? false), 403);
        }
    }
}
