<?php

namespace App\Services\Coding;

use App\Events\Coding\CodingSessionEventBroadcasted;
use App\Models\CodingAssignment;
use App\Models\CodingSession;
use App\Models\CodingSessionEvent;
use App\Models\CodingSessionFile;
use App\Models\CodingSessionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CodingStudioService
{
    public function tablesReady(): bool
    {
        foreach ([
            'coding_sessions',
            'session_participants',
            'coding_session_files',
            'coding_session_messages',
            'coding_session_events',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    public function isTeacher(CodingSession $session, User $user, ?CodingSessionParticipant $participant = null): bool
    {
        return $user->isSuperAdmin()
            || $session->teacher_id === $user->id
            || in_array($participant?->role_in_session, ['teacher', 'assistant'], true);
    }

    public function defaultPermissions(CodingSession $session, bool $isTeacher = false): array
    {
        return [
            'edit' => $isTeacher,
            'chat' => true,
            'pointer' => true,
            'code' => $isTeacher,
            'preview' => true,
            'submit' => ! $isTeacher,
            'highlight' => $isTeacher,
        ];
    }

    public function normalizePermissions(array $permissions, CodingSession $session, bool $isTeacher = false): array
    {
        $defaults = $this->defaultPermissions($session, $isTeacher);

        foreach ($defaults as $key => $value) {
            $permissions[$key] = (bool) ($permissions[$key] ?? $value);
        }

        return $permissions;
    }

    public function participantPermissions(CodingSession $session, User $user, ?CodingSessionParticipant $participant = null): array
    {
        $participant ??= $session->participants()->where('user_id', $user->id)->latest('joined_at')->first();
        $isTeacher = $this->isTeacher($session, $user, $participant);

        $sessionPermissions = $session->permissions ?? [];
        $participantPermissions = $participant?->permissions ?? [];

        $permissions = array_merge(
            $this->defaultPermissions($session, $isTeacher),
            $sessionPermissions,
            $participantPermissions
        );

        if ($isTeacher) {
            $permissions['edit'] = true;
            $permissions['code'] = true;
            $permissions['highlight'] = true;
        }

        return $this->normalizePermissions($permissions, $session, $isTeacher);
    }

    public function ensureParticipant(CodingSession $session, User $user, string $role, ?array $permissions = null): CodingSessionParticipant
    {
        $participant = CodingSessionParticipant::where('coding_session_id', $session->id)
            ->where('user_id', $user->id)
            ->first();

        $permissions = $this->normalizePermissions(
            $permissions ?? $this->defaultPermissions($session, $role === 'teacher'),
            $session,
            $role === 'teacher'
        );

        if (! $participant) {
            $participant = CodingSessionParticipant::create([
                'school_id' => $session->school_id,
                'coding_session_id' => $session->id,
                'user_id' => $user->id,
                'role_in_session' => $role,
                'joined_at' => now(),
                'is_active' => true,
                'permissions' => $permissions,
            ]);

            $this->recordEvent($session, 'participant.joined', $user, 'Participant joined', null, [
                'user_id' => $user->id,
                'user_name' => $user->displayName(),
                'role_in_session' => $role,
            ]);

            return $participant;
        }

        $updates = [
            'role_in_session' => $role,
            'is_active' => true,
            'joined_at' => now(),
            'left_at' => null,
            'permissions' => $permissions,
        ];

        $participant->update($updates);

        return $participant;
    }

    public function starterFilesForSession(CodingSession $session, ?CodingAssignment $assignment = null): array
    {
        $mode = $session->lesson_mode ?: 'coding';
        $assignment ??= $session->assignment;
        $starterHtml = $assignment?->starter_html ?? "<h1>Welcome to the coding studio</h1>\n<p>Teach, explain, and build together here.</p>";
        $starterCss = $assignment?->starter_css ?? "body{font-family:sans-serif;padding:24px;background:#f8fafc;color:#0f172a;}";
        $starterJs = $assignment?->starter_js ?? "console.log('ClassBridge AI coding studio ready.');";

        return match ($mode) {
            'php' => [
                ['filename' => 'index.php', 'language' => 'php', 'content' => $assignment?->starter_html ? $assignment->starter_html : "<?php\n\necho 'Hello from ClassBridge AI';\n", 'sort_order' => 1, 'is_entry_point' => true],
                ['filename' => 'style.css', 'language' => 'css', 'content' => $starterCss, 'sort_order' => 2, 'is_entry_point' => false],
                ['filename' => 'notes.md', 'language' => 'markdown', 'content' => "### Lesson notes\n\n- Explain the idea\n- Code together\n- Review the output", 'sort_order' => 3, 'is_entry_point' => false],
            ],
            'html', 'javascript', 'coding', 'mixed' => [
                ['filename' => 'index.html', 'language' => 'html', 'content' => $starterHtml, 'sort_order' => 1, 'is_entry_point' => true],
                ['filename' => 'style.css', 'language' => 'css', 'content' => $starterCss, 'sort_order' => 2, 'is_entry_point' => false],
                ['filename' => 'script.js', 'language' => 'javascript', 'content' => $starterJs, 'sort_order' => 3, 'is_entry_point' => false],
                ['filename' => 'lesson-notes.md', 'language' => 'markdown', 'content' => "### Lesson steps\n\n1. Read the goal\n2. Change the code\n3. Run the preview", 'sort_order' => 4, 'is_entry_point' => false],
            ],
            default => [
                ['filename' => 'notes.md', 'language' => 'markdown', 'content' => "### Lesson steps\n\n1. Read the brief\n2. Edit the starter code\n3. Ask for help if needed", 'sort_order' => 1, 'is_entry_point' => true],
            ],
        };
    }

    public function seedFiles(CodingSession $session, ?CodingAssignment $assignment = null): void
    {
        if ($session->files()->exists()) {
            return;
        }

        foreach ($this->starterFilesForSession($session, $assignment) as $file) {
            CodingSessionFile::create([
                'school_id' => $session->school_id,
                'coding_session_id' => $session->id,
                'filename' => $file['filename'],
                'language' => $file['language'],
                'content' => $file['content'],
                'sort_order' => $file['sort_order'],
                'is_entry_point' => $file['is_entry_point'],
                'created_by' => $session->created_by,
                'updated_by' => $session->created_by,
                'version' => 1,
            ]);
        }

        $session->active_file_key = $session->active_file_key ?: $session->files()->orderBy('sort_order')->value('filename');
        $session->save();
    }

    public function workspaceSnapshot(CodingSession $session): array
    {
        return [
            'id' => $session->id,
            'title' => $session->title,
            'join_code' => $session->join_code,
            'status' => $session->status,
            'lesson_mode' => $session->lesson_mode,
            'active_file_key' => $session->active_file_key,
            'permissions' => $session->permissions ?? [],
            'lesson_steps' => $session->lesson_steps ?? [],
            'metadata' => $session->metadata ?? [],
            'started_at' => $session->started_at,
            'ended_at' => $session->ended_at,
            'last_saved_at' => $session->last_saved_at,
            'participants' => $session->participants()->with('user')->latest('joined_at')->get(),
            'files' => $session->files()->orderBy('sort_order')->get(),
            'messages' => $session->messages()->with('user')->latest()->take(50)->get()->reverse()->values(),
            'events' => $session->events()->with('user')->latest('occurred_at')->take(20)->get()->reverse()->values(),
        ];
    }

    public function storeFileDraft(CodingSession $session, CodingSessionFile $file, string $content, ?User $actor = null): CodingSessionFile
    {
        $file->content = $content;
        $file->version = (int) $file->version + 1;
        $file->updated_by = $actor?->id;
        $file->save();

        $session->active_file_key = $file->filename;
        $session->last_saved_at = now();
        $session->save();

        $this->recordEvent($session, 'code.updated', $actor, 'Code updated', null, [
            'file_id' => $file->id,
            'filename' => $file->filename,
            'language' => $file->language,
            'content' => $content,
        ]);

        return $file;
    }

    public function saveSessionSnapshot(CodingSession $session, ?User $actor = null): array
    {
        $session->last_saved_at = now();
        $session->save();

        $this->recordEvent($session, 'code.saved', $actor, 'Session saved', 'Teacher saved the live coding session snapshot.', [
            'active_file_key' => $session->active_file_key,
        ]);

        return $this->workspaceSnapshot($session);
    }

    public function recordEvent(CodingSession $session, string $eventType, ?User $actor, string $title, ?string $description = null, array $payload = []): CodingSessionEvent
    {
        $event = CodingSessionEvent::create([
            'school_id' => $session->school_id,
            'coding_session_id' => $session->id,
            'user_id' => $actor?->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
            'occurred_at' => now(),
        ]);

        event(new CodingSessionEventBroadcasted(
            $session->id,
            $eventType,
            array_merge([
                'event_id' => $event->id,
                'title' => $title,
                'description' => $description,
                'user_id' => $actor?->id,
                'user_name' => $actor?->displayName(),
                'occurred_at' => $event->occurred_at,
            ], $payload)
        ));

        return $event;
    }

    public function classroomTitle(CodingSession $session): string
    {
        return trim($session->title ?: 'Live Coding Studio');
    }

    public function slugifyTitle(string $title): string
    {
        return Str::slug($title) ?: 'live-coding-studio';
    }
}
