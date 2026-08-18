<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardPageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $whiteboardId,
        public int $pageId,
        public string $pageKey,
        public string $title,
        public int $pageNumber,
        public int $userId,
        public string $userName,
        public array $whiteboardState = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'whiteboard.page.created';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'whiteboard_id' => $this->whiteboardId,
            'page_id' => $this->pageId,
            'page_key' => $this->pageKey,
            'title' => $this->title,
            'page_number' => $this->pageNumber,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'whiteboard_state' => $this->whiteboardState,
        ];
    }
}
