<?php
namespace App\Events\Classroom;

use App\Models\ClassroomMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ClassroomMessage $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('classroom.' . $this->message->classroom_session_id)];
    }

    public function broadcastAs(): string { return 'classroom.message.sent'; }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->message->classroom_session_id,
            'id' => $this->message->id,
            'user_id' => $this->message->user_id,
            'user_name' => $this->message->user?->displayName(),
            'message_type' => $this->message->message_type,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at,
        ];
    }
}
