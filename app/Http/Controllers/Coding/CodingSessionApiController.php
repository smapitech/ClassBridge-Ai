<?php

namespace App\Http\Controllers\Coding;

use App\Events\Coding\CodingSessionEventBroadcasted;
use App\Http\Controllers\Controller;
use App\Models\CodingSession;
use App\Models\CodingSessionFile;
use App\Models\CodingSessionMessage;
use App\Models\CodingSessionParticipant;
use App\Services\Coding\CodingStudioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CodingSessionApiController extends Controller
{
    public function __construct(protected CodingStudioService $studio) {}

    protected function sessionOrFail($sessionId): CodingSession
    {
        $session = CodingSession::findOrFail($sessionId);

        if (! Auth::user()->isSuperAdmin() && $session->school_id !== Auth::user()->school_id) {
            abort(403);
        }

        return $session;
    }

    protected function currentParticipant(CodingSession $session): ?CodingSessionParticipant
    {
        return $session->participants()
            ->where('user_id', Auth::id())
            ->latest('joined_at')
            ->first();
    }

    protected function ensureTeacher(CodingSession $session): void
    {
        abort_unless($this->studio->isTeacher($session, Auth::user(), $this->currentParticipant($session)), 403);
    }

    protected function ensurePermission(CodingSession $session, string $key): void
    {
        $permissions = $this->studio->participantPermissions($session, Auth::user(), $this->currentParticipant($session));
        abort_unless((bool) ($permissions[$key] ?? false), 403);
    }

    public function state($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);

        return response()->json($this->studio->workspaceSnapshot($session));
    }

    public function saveFile(Request $request, $sessionId, CodingSessionFile $file)
    {
        $session = $this->sessionOrFail($sessionId);
        abort_unless((int) $file->coding_session_id === (int) $session->id, 404);
        $this->ensurePermission($session, 'edit');

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $content = (string) ($validated['content'] ?? '');
        $file = $this->studio->storeFileDraft($session, $file, $content, Auth::user());

        return response()->json([
            'success' => true,
            'file' => $file,
            'saved_at' => $session->fresh()->last_saved_at,
        ]);
    }

    public function createFile(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:60'],
        ]);

        $lastOrder = (int) $session->files()->max('sort_order');

        $file = CodingSessionFile::create([
            'school_id' => $session->school_id,
            'coding_session_id' => $session->id,
            'filename' => $validated['filename'],
            'language' => $validated['language'] ?? 'plaintext',
            'content' => '',
            'sort_order' => $lastOrder + 1,
            'is_entry_point' => false,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'version' => 1,
        ]);

        $this->studio->recordEvent($session, 'file.created', Auth::user(), 'File created', 'A new lesson file was added to the coding session.', [
            'file_id' => $file->id,
            'filename' => $file->filename,
            'language' => $file->language,
        ]);

        return response()->json([
            'success' => true,
            'file' => $file,
        ]);
    }

    public function setActiveFile(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'code');

        $validated = $request->validate([
            'active_file_key' => ['required', 'string', 'max:255'],
        ]);

        $session->active_file_key = $validated['active_file_key'];
        $session->save();

        $this->studio->recordEvent($session, 'file.changed', Auth::user(), 'Active file changed', null, [
            'active_file_key' => $session->active_file_key,
        ]);

        return response()->json([
            'success' => true,
            'active_file_key' => $session->active_file_key,
        ]);
    }

    public function sendMessage(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'chat');

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'message_type' => ['nullable', 'string', 'max:40'],
        ]);

        $message = CodingSessionMessage::create([
            'school_id' => $session->school_id,
            'coding_session_id' => $session->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'text',
            'metadata' => [
                'display_name' => Auth::user()->displayName(),
            ],
        ]);

        $message->load('user');

        event(new CodingSessionEventBroadcasted($session->id, 'chat.message.sent', [
            'id' => $message->id,
            'message' => $message->message,
            'message_type' => $message->message_type,
            'user_id' => $message->user_id,
            'user_name' => $message->user?->displayName(),
            'created_at' => $message->created_at,
        ]));

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function fetchMessages(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $query = $session->messages()->with('user')->latest();

        if ($request->since_id) {
            $query->where('id', '>', $request->since_id);
        }

        return response()->json([
            'messages' => $query->take(50)->get()->reverse()->values(),
        ]);
    }

    public function updateCursor(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensurePermission($session, 'pointer');

        $validated = $request->validate([
            'cursor_line' => ['nullable', 'integer', 'min:1'],
            'cursor_column' => ['nullable', 'integer', 'min:1'],
            'active_file_key' => ['nullable', 'string', 'max:255'],
            'typing_status' => ['nullable', 'string', 'max:40'],
        ]);

        $participant = $this->currentParticipant($session);
        if ($participant) {
            $participant->update([
                'cursor_line' => $validated['cursor_line'] ?? $participant->cursor_line,
                'cursor_column' => $validated['cursor_column'] ?? $participant->cursor_column,
                'active_file_key' => $validated['active_file_key'] ?? $participant->active_file_key,
                'typing_status' => $validated['typing_status'] ?? $participant->typing_status,
            ]);
        }

        $this->studio->recordEvent($session, 'cursor.moved', Auth::user(), 'Cursor moved', null, [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->displayName(),
            'cursor_line' => $validated['cursor_line'] ?? null,
            'cursor_column' => $validated['cursor_column'] ?? null,
            'active_file_key' => $validated['active_file_key'] ?? $session->active_file_key,
            'typing_status' => $validated['typing_status'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    public function updatePermissions(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'participant_id' => ['nullable', 'integer', 'exists:session_participants,id'],
        ]);

        $permissions = $this->studio->normalizePermissions(
            $validated['permissions'],
            $session,
            true
        );

        $participant = $validated['participant_id']
            ? CodingSessionParticipant::where('coding_session_id', $session->id)->findOrFail($validated['participant_id'])
            : null;

        if ($participant) {
            $participant->update(['permissions' => $permissions]);
        } else {
            $session->permissions = $permissions;
            $session->save();
        }

        $this->studio->recordEvent($session, 'student.permission.changed', Auth::user(), 'Permissions updated', 'Teacher updated session permissions.', [
            'permissions' => $permissions,
            'participant_id' => $participant?->id,
        ]);

        return response()->json([
            'success' => true,
            'permissions' => $permissions,
        ]);
    }

    public function takeControl(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $session->metadata = array_merge($session->metadata ?? [], [
            'editor_controller_id' => Auth::id(),
            'editor_controller_name' => Auth::user()->displayName(),
        ]);
        $session->save();

        $this->studio->recordEvent($session, 'control.changed', Auth::user(), 'Teacher took control', 'The teacher is editing the live code now.', [
            'editor_controller_id' => Auth::id(),
            'editor_controller_name' => Auth::user()->displayName(),
            'mode' => 'teacher',
        ]);

        return response()->json(['success' => true]);
    }

    public function releaseControl(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $session->metadata = array_merge($session->metadata ?? [], [
            'editor_controller_id' => null,
            'editor_controller_name' => null,
        ]);
        $session->save();

        $this->studio->recordEvent($session, 'control.changed', Auth::user(), 'Control released', 'Editing was returned to the learner.', [
            'editor_controller_id' => null,
            'editor_controller_name' => null,
            'mode' => 'student',
        ]);

        return response()->json(['success' => true]);
    }

    public function highlightSelection(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'file_id' => ['nullable', 'integer', 'exists:coding_session_files,id'],
            'line_start' => ['required', 'integer', 'min:1'],
            'line_end' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = [
            'file_id' => $validated['file_id'] ?? null,
            'line_start' => $validated['line_start'],
            'line_end' => $validated['line_end'],
            'note' => $validated['note'] ?? null,
        ];

        $this->studio->recordEvent($session, 'line.highlighted', Auth::user(), 'Code highlighted', $validated['note'] ?? 'Teacher highlighted a block of code.', $payload);

        return response()->json(['success' => true]);
    }

    public function updateLessonSteps(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $validated = $request->validate([
            'lesson_steps' => ['required', 'array'],
            'lesson_steps.*.title' => ['required', 'string', 'max:255'],
            'lesson_steps.*.description' => ['nullable', 'string', 'max:1000'],
            'lesson_steps.*.is_done' => ['nullable', 'boolean'],
        ]);

        $session->lesson_steps = array_values($validated['lesson_steps']);
        $session->save();

        $this->studio->recordEvent($session, 'lesson.steps.updated', Auth::user(), 'Lesson steps updated', 'Teacher updated the guided lesson steps.', [
            'lesson_steps' => $session->lesson_steps,
        ]);

        return response()->json([
            'success' => true,
            'lesson_steps' => $session->lesson_steps,
        ]);
    }

    public function raiseHand(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $participant = $this->currentParticipant($session);
        if ($participant) {
            $participant->update(['typing_status' => 'raising_hand']);
        }

        $this->studio->recordEvent($session, 'student.raise_hand', Auth::user(), 'Student raised hand', 'A learner requested help.', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->displayName(),
        ]);

        return response()->json(['success' => true]);
    }

    public function requestHelp(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $participant = $this->currentParticipant($session);
        if ($participant) {
            $participant->update(['typing_status' => 'requesting_help']);
        }

        $this->studio->recordEvent($session, 'student.request_help', Auth::user(), 'Student requested help', 'A learner asked for teacher guidance.', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->displayName(),
        ]);

        return response()->json(['success' => true]);
    }

    public function submitWork(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $participant = $this->currentParticipant($session);

        if (! $participant || $participant->user_id !== Auth::id()) {
            abort(403);
        }

        $this->studio->recordEvent($session, 'work.submitted', Auth::user(), 'Work submitted', 'The student submitted the live coding task.', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->displayName(),
        ]);

        return response()->json(['success' => true]);
    }

    public function saveSession(Request $request, $sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $this->ensureTeacher($session);

        $snapshot = $this->studio->saveSessionSnapshot($session, Auth::user());

        return response()->json([
            'success' => true,
            'saved_at' => data_get($snapshot, 'last_saved_at'),
            'files' => count($snapshot['files'] ?? []),
        ]);
    }

    public function leave($sessionId)
    {
        $session = $this->sessionOrFail($sessionId);
        $participant = $this->currentParticipant($session);

        if ($participant && $participant->is_active) {
            $participant->update([
                'is_active' => false,
                'left_at' => now(),
            ]);

            $this->studio->recordEvent($session, 'participant.left', Auth::user(), 'Participant left', null, [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->displayName(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
