<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardCleared implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $userId,
        public string $userName,
        public ?string $pageKey = null,
        public int $deletedCount = 0
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'whiteboard.cleared';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'page_key' => $this->pageKey,
            'deleted_count' => $this->deletedCount,
        ];
    }
}
