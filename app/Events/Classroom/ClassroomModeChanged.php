<?php
namespace App\Events\Classroom;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomModeChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public string $mode,
        public ?array $modeSettings = null,
        public ?int $userId = null,
        public ?string $userName = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->sessionId)];
    }

    public function broadcastAs(): string { return 'classroom.mode.changed'; }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'mode' => $this->mode,
            'mode_settings' => $this->modeSettings ?? [],
            'user_id' => $this->userId,
            'user_name' => $this->userName,
        ];
    }
}
