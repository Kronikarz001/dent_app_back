<?php

namespace Tests\Unit\Events;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Tests\TestCase;

/**
 * Summary of MessageSentTest
 */
class MessageSentTest extends TestCase
{
    /**
     * @return void
     */
    public function testBroadcastOnPrivateMessageUsesRecipientUserChannel(): void
    {
        $message = Message::factory()->make([
            'recipient_user_uuid' => 'recipient-uuid',
            'message_group_uuid' => null,
        ]);

        $channels = (new MessageSent($message))->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-App.Models.User.recipient-uuid', $channels[0]->name);
    }

    /**
     * @return void
     */
    public function testBroadcastOnGroupMessageUsesGroupChannel(): void
    {
        $message = Message::factory()->make([
            'message_group_uuid' => 'group-uuid',
        ]);

        $channels = (new MessageSent($message))->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-message-group.group-uuid', $channels[0]->name);
    }

    /**
     * @return void
     */
    public function testBroadcastAsReturnsMessageSentEventName(): void
    {
        $message = Message::factory()->make();

        $this->assertSame('message.sent', (new MessageSent($message))->broadcastAs());
    }
}
