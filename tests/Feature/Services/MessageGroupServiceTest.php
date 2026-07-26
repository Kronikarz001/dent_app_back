<?php

namespace Tests\Feature\Services;

use App\Models\Message;
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
}
