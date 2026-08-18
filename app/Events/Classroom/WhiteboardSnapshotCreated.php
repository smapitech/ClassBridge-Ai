<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardSnapshotCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $whiteboardId,
        public int $snapshotId,
        public int $userId,
        public string $userName,
        public ?string $name = null,
        public ?string $reason = null,
        public ?string $pageKey = null,
        public array $whiteboardState = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'whiteboard.snapshot.created';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'whiteboard_id' => $this->whiteboardId,
            'snapshot_id' => $this->snapshotId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'name' => $this->name,
            'reason' => $this->reason,
            'page_key' => $this->pageKey,
            'whiteboard_state' => $this->whiteboardState,
        ];
    }
}
