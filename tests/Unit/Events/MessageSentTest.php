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
    public function testBroadcastOnPrivateMessageUsesRecipientAndSenderUserChannels(): void
    {
        $message = Message::factory()->make([
            'user_uuid' => 'sender-uuid',
            'recipient_user_uuid' => 'recipient-uuid',
            'message_group_uuid' => null,
        ]);

        $channels = (new MessageSent($message))->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertContainsOnlyInstancesOf(PrivateChannel::class, $channels);
        $channelNames = array_map(fn (PrivateChannel $channel) => $channel->name, $channels);
        $this->assertContains('private-App.Models.User.recipient-uuid', $channelNames);
        $this->assertContains('private-App.Models.User.sender-uuid', $channelNames);
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
