<?php
namespace App\Events\Classroom;

use App\Models\WhiteboardElement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardElementCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WhiteboardElement $element) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->element->classroom_session_id)];
    }

    public function broadcastAs(): string { return 'whiteboard.element.created'; }

    /** Only send to other users, not the creator */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->element->classroom_session_id,
            'id' => $this->element->id,
            'user_id' => $this->element->user_id,
            'user_name' => $this->element->user?->displayName(),
            'element_type' => $this->element->element_type,
            'data' => $this->element->data,
            'created_at' => $this->element->created_at,
        ];
    }
}
