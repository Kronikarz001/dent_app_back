<?php

namespace Tests\Feature\Controllers;

use App\Models\MessageGroup;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of MessageGroupControllerTest
 */
class MessageGroupControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testShowForbiddenWhenNotMember(): void
    {
        $group = MessageGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('messageGroup.show', ['messageGroup' => $group->uuid]));

        $response->assertForbidden();
    }

    /**
     * @return void
     */
    public function testShowReturnsGroupForMember(): void
    {
        $member = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach($member->uuid);

        $response = $this->callApiWithLoggedUser($member)
            ->getJson(route('messageGroup.show', ['messageGroup' => $group->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $group->uuid);
    }

    /**
     * @return void
     */
    public function testUpdateForbiddenWhenNotMember(): void
    {
        $group = MessageGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('messageGroup.update', ['messageGroup' => $group->uuid]), ['name' => 'Nowa']);

        $response->assertForbidden();
    }

    /**
     * @return void
     */
    public function testDestroyForbiddenWhenNotMember(): void
    {
        $group = MessageGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->deleteJson(route('messageGroup.destroy', ['messageGroup' => $group->uuid]));

        $response->assertForbidden();
        $this->assertDatabaseHas('message_groups', ['uuid' => $group->uuid]);
    }

    /**
     * @return void
     */
    public function testAddUserForbiddenWhenRequesterIsNotMember(): void
    {
        $group = MessageGroup::factory()->create();
        $newUser = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('messageGroup.addUser', ['messageGroup' => $group->uuid, 'user' => $newUser->uuid]));

        $response->assertForbidden();
    }

    /**
     * @return void
     */
    public function testRemoveUserNotFoundWhenTargetIsNotMember(): void
    {
        $member = User::factory()->create();
        $notMember = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach([$member->uuid, User::factory()->create()->uuid]);

        $response = $this->callApiWithLoggedUser($member)
            ->deleteJson(route('messageGroup.removeUser', ['messageGroup' => $group->uuid, 'user' => $notMember->uuid]));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testMarkAsReadForbiddenWhenNotMember(): void
    {
        $group = MessageGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('messageGroup.markAsRead', ['messageGroup' => $group->uuid]));

        $response->assertForbidden();
    }
}
