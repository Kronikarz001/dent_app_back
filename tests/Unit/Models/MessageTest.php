<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MessageTest
 */
final class MessageTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUnreadIsFalseWhenNoUserIsAuthenticated(): void
    {
        $message = Message::factory()->create();

        Auth::shouldReceive('user')->once()->andReturn(null);

        $this->assertFalse($message->unread);
    }

    /**
     * @return void
     */
    public function testUnreadIsFalseForOwnMessage(): void
    {
        $sender = User::factory()->create();
        $message = Message::factory()->create(['user_uuid' => $sender->uuid]);

        Auth::shouldReceive('user')->once()->andReturn($sender);

        $this->assertFalse($message->unread);
    }

    /**
     * @return void
     */
    public function testUnreadIsTrueWhenRecipientHasNotReadIt(): void
    {
        $recipient = User::factory()->create();
        $message = Message::factory()->create();

        Auth::shouldReceive('user')->once()->andReturn($recipient);

        $this->assertTrue($message->unread);
    }

    /**
     * @return void
     */
    public function testUnreadIsFalseWhenRecipientHasReadIt(): void
    {
        $recipient = User::factory()->create();
        $message = Message::factory()->create();
        $message->readBy()->attach($recipient->uuid);

        Auth::shouldReceive('user')->once()->andReturn($recipient);

        $this->assertFalse($message->unread);
    }

    /**
     * @return void
     */
    public function testSenderReturnsOwningUser(): void
    {
        $sender = User::factory()->create();
        $message = Message::factory()->create(['user_uuid' => $sender->uuid]);

        $this->assertTrue($message->sender->is($sender));
    }

    /**
     * @return void
     */
    public function testRecipientReturnsAssignedUser(): void
    {
        $recipient = User::factory()->create();
        $message = Message::factory()->create(['recipient_user_uuid' => $recipient->uuid]);

        $this->assertTrue($message->recipient->is($recipient));
    }

    /**
     * @return void
     */
    public function testGroupReturnsAssignedMessageGroup(): void
    {
        $group = MessageGroup::factory()->create();
        $message = Message::factory()->create(['message_group_uuid' => $group->uuid]);

        $this->assertTrue($message->group->is($group));
    }
}
