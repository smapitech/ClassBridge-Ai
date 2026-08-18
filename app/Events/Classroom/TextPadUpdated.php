<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TextPadUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public string $content,
        public int $userId,
        public string $userName,
        public array $comments = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'classroom.textpad.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'content' => $this->content,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'comments' => $this->comments,
        ];
    }
}
