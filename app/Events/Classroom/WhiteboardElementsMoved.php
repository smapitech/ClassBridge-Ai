<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardElementsMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $whiteboardId,
        public int $userId,
        public string $userName,
        public array $elements = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'whiteboard.elements.moved';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'whiteboard_id' => $this->whiteboardId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'elements' => $this->elements,
        ];
    }
}
