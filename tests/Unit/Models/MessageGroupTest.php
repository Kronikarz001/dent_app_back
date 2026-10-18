<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MessageGroupTest
 */
final class MessageGroupTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testIsDefaultIsCastToBoolean(): void
    {
        $group = MessageGroup::factory()->create(['is_default' => 1]);

        $this->assertTrue($group->is_default);
    }

    /**
     * @return void
     */
    public function testCreatorReturnsAssignedUser(): void
    {
        $creator = User::factory()->create();
        $group = MessageGroup::factory()->create(['creator_uuid' => $creator->uuid]);

        $this->assertTrue($group->creator->is($creator));
    }

    /**
     * @return void
     */
    public function testUsersReturnsAttachedUsers(): void
    {
        $group = MessageGroup::factory()->create();
        $user = User::factory()->create();

        $group->users()->attach($user->uuid);

        $this->assertTrue($group->users->contains($user));
    }

    /**
     * @return void
     */
    public function testMessagesReturnsGroupsMessages(): void
    {
        $group = MessageGroup::factory()->create();
        $message = Message::factory()->create(['message_group_uuid' => $group->uuid]);
        Message::factory()->create();

        $this->assertTrue($group->messages->contains($message));
        $this->assertCount(1, $group->messages);
    }
}
