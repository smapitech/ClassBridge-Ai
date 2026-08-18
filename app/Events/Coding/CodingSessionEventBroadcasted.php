<?php

namespace App\Events\Coding;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CodingSessionEventBroadcasted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public string $eventType,
        public array $payload = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('coding.' . $this->sessionId)];
    }

    public function broadcastAs(): string
    {
        return 'coding.' . ltrim($this->eventType, '.');
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'session_id' => $this->sessionId,
            'event_type' => $this->eventType,
        ], $this->payload);
    }
}
