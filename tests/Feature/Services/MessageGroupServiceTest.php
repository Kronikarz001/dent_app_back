<?php

namespace Tests\Feature\Services;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Services\MessageGroupServiceInterface;
use App\Services\MessageServiceInterface;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Summary of MessageGroupServiceTest
 */
class MessageGroupServiceTest extends TestCase
{
    private MessageGroupServiceInterface $groupService;

    private MessageServiceInterface $messageService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->groupService = app(MessageGroupServiceInterface::class);
        $this->messageService = app(MessageServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetGroupMessagesReturnsOnlyMessagesFromThatGroup(): void
    {
        $sender = User::factory()->create();
        Auth::setUser($sender);
        $root = $this->messageService->send(['message' => 'Pierwsza']);
        $reply = $this->messageService->send(['message' => 'Druga', 'message_group_uuid' => $root->message_group_uuid]);
        $unrelated = Message::factory()->create(['user_uuid' => $sender->uuid]);

        $result = $this->groupService->getGroupMessages($root->group);

        $uuids = $result->getCollection()->pluck('uuid')->all();
        $this->assertContains($root->uuid, $uuids);
        $this->assertContains($reply->uuid, $uuids);
        $this->assertNotContains($unrelated->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testMarkGroupAsReadCreatesReadRowsForGroupMessages(): void
    {
        $user = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach($user->uuid);
        $message = Message::factory()->create(['message_group_uuid' => $group->uuid]);
        Auth::setUser($user);

        $this->groupService->markGroupAsRead($group);

        $this->assertDatabaseHas('message_reads', ['message_uuid' => $message->uuid, 'user_uuid' => $user->uuid]);
    }
}
