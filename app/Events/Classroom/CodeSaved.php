<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CodeSaved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public string $code,
        public int $userId,
        public string $userName,
        public ?string $savedAt = null,
        public ?string $language = null,
        public ?array $files = null,
        public ?string $activeFileKey = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'code.saved';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'code' => $this->code,
            'saved_at' => $this->savedAt,
            'language' => $this->language,
            'files' => $this->files ?? [],
            'active_file_key' => $this->activeFileKey,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
        ];
    }
}
