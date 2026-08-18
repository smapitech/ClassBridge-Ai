<?php

namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardBackgroundChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $whiteboardId,
        public int $pageId,
        public string $pageKey,
        public int $userId,
        public string $userName,
        public ?string $backgroundType = null,
        public ?string $backgroundValue = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'whiteboard.background.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'whiteboard_id' => $this->whiteboardId,
            'page_id' => $this->pageId,
            'page_key' => $this->pageKey,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'background_type' => $this->backgroundType,
            'background_value' => $this->backgroundValue,
        ];
    }
}
