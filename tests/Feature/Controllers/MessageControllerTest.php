<?php

namespace Tests\Feature\Controllers;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of MessageControllerTest
 */
class MessageControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexReturnsInboxForLoggedUser(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $received = Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $user->uuid]);
        Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $other->uuid]);

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('message.index'));

        $response->assertOk();
        $uuids = collect($response->json('data'))->pluck('uuid')->all();
        $this->assertContains($received->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testStoreDirectMessageReturnsCreatedResponse(): void
    {
        $recipient = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('message.store'), [
                'message' => 'Hej',
                'recipient_uuid' => $recipient->uuid,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'message' => 'Hej',
            'recipient_user_uuid' => $recipient->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testStoreBroadcastMessageReturnsCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('message.store'), [
                'message' => 'Do wszystkich',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Do wszystkich');
        $this->assertNotNull($response->json('message_group_uuid'));
    }

    /**
     * @return void
     */
    public function testStoreFailsValidationWhenBothRecipientAndGroupProvided(): void
    {
        $recipient = User::factory()->create();
        $group = MessageGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('message.store'), [
                'message' => 'Hej',
                'recipient_uuid' => $recipient->uuid,
                'message_group_uuid' => $group->uuid,
            ]);

        $response->assertUnprocessable();
    }

    /**
     * @return void
     */
    public function testStoreFailsWhenSenderIsNotGroupMember(): void
    {
        $group = MessageGroup::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('message.store'), [
                'message' => 'Hej',
                'message_group_uuid' => $group->uuid,
            ]);

        $response->assertForbidden();
    }

    /**
     * @return void
     */
    public function testDestroyRemovesOwnMessage(): void
    {
        $user = User::factory()->create();
        $message = Message::factory()->create(['user_uuid' => $user->uuid]);

        $response = $this->callApiWithLoggedUser($user)
            ->deleteJson(route('message.destroy', ['message' => $message->uuid]));

        $response->assertNoContent();
        $this->assertDatabaseMissing('messages', ['uuid' => $message->uuid]);
    }

    /**
     * @return void
     */
    public function testDestroyForbiddenWhenNotOwner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $message = Message::factory()->create(['user_uuid' => $owner->uuid]);

        $response = $this->callApiWithLoggedUser($other)
            ->deleteJson(route('message.destroy', ['message' => $message->uuid]));

        $response->assertForbidden();
        $this->assertDatabaseHas('messages', ['uuid' => $message->uuid]);
    }

    /**
     * @return void
     */
    public function testIndexSearchStringFiltersInboxByMessageContent(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $target = Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $user->uuid, 'message' => 'Wyjatkowa tresc']);
        Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $user->uuid, 'message' => 'Inna tresc']);

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('message.index', ['searchString' => 'Wyjatkowa']));

        $response->assertOk();
        $uuids = collect($response->json('data'))->pluck('uuid')->all();
        $this->assertSame([$target->uuid], $uuids);
    }

    /**
     * @return void
     */
    public function testGroupMessagesReturnsConversation(): void
    {
        $sender = User::factory()->create();
        $message = Message::factory()->create(['user_uuid' => $sender->uuid]);
        $group = MessageGroup::factory()->create();
        $message->update(['message_group_uuid' => $group->uuid]);
        $group->users()->attach($sender->uuid);

        $response = $this->callApiWithLoggedUser($sender)
            ->getJson(route('messageGroup.messages', ['messageGroup' => $group->uuid]));

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $message->uuid);
    }

    /**
     * @return void
     */
    public function testUnreadCountReturnsNumberOfUnreadConversations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $user->uuid]);

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('message.unreadCount'));

        $response->assertOk();
        $response->assertJsonPath('count', 1);
    }
}
