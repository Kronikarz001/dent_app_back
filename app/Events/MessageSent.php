<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Summary of MessageSent
 */
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable;

    /**
     * @param Message $message
     */
    public function __construct(
        public readonly Message $message,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->message->message_group_uuid !== null) {
            return [new PrivateChannel('message-group.'.$this->message->message_group_uuid)];
        }

        return [new PrivateChannel('App.Models.User.'.$this->message->recipient_user_uuid)];
    }

    /**
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * @return array
     */
    public function broadcastWith(): array
    {
        return (new MessageResource($this->message->load(['sender', 'recipient', 'group'])))->resolve();
    }
}
