<?php
namespace App\Events\Classroom;

use App\Models\PointerEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PointerMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PointerEvent $pointer) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->pointer->classroom_session_id)];
    }

    public function broadcastAs(): string { return 'pointer.moved'; }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->pointer->classroom_session_id,
            'pointer_id' => $this->pointer->id,
            'user_id' => $this->pointer->user_id,
            'x_position' => (float) $this->pointer->x_position,
            'y_position' => (float) $this->pointer->y_position,
            'target_area' => $this->pointer->target_area,
            'user_name' => $this->pointer->user?->displayName(),
        ];
    }
}
