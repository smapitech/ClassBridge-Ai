<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardElementDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $elementId,
        public int $userId,
        public string $userName,
        public ?string $elementType = null,
        public ?string $pageKey = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'whiteboard.element.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'id' => $this->elementId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'element_type' => $this->elementType,
            'page_key' => $this->pageKey,
        ];
    }
}
