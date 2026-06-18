<?php

namespace Tests\Feature\Services;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Services\MessageServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Summary of MessageServiceTest
 */
class MessageServiceTest extends TestCase
{
    /**
     * @var MessageServiceInterface|Application|mixed|object
     */
    private MessageServiceInterface $service;

    protected const MESSAGES_TABLE = 'messages';

    protected const MESSAGE_GROUPS_TABLE = 'message_groups';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MessageServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testSendDirectMessagePersistsRecipient(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        Auth::setUser($sender);

        $message = $this->service->send(['message' => 'Hej', 'recipient_uuid' => $recipient->uuid]);

        $this->assertDatabaseHas(self::MESSAGES_TABLE, [
            'uuid' => $message->uuid,
            'user_uuid' => $sender->uuid,
            'recipient_user_uuid' => $recipient->uuid,
            'message_group_uuid' => null,
        ]);
    }

    /**
     * @return void
     */
    public function testSendWithoutRecipientCreatesGroupAndLinksRootMessage(): void
    {
        $sender = User::factory()->create();
        Auth::setUser($sender);

        $message = $this->service->send(['message' => 'Do wszystkich']);

        $this->assertNotNull($message->message_group_uuid);
        $this->assertDatabaseHas(self::MESSAGE_GROUPS_TABLE, [
            'uuid' => $message->message_group_uuid,
            'message_uuid' => $message->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testSendToExistingGroupReusesGroup(): void
    {
        $sender = User::factory()->create();
        Auth::setUser($sender);
        $rootMessage = $this->service->send(['message' => 'Pierwsza']);

        $reply = $this->service->send(['message' => 'Druga', 'message_group_uuid' => $rootMessage->message_group_uuid]);

        $this->assertSame($rootMessage->message_group_uuid, $reply->message_group_uuid);
        $this->assertSame(1, MessageGroup::where('uuid', $rootMessage->message_group_uuid)->count());
    }

    /**
     * @return void
     */
    public function testGetInboxIncludesReceivedSentAndBroadcastMessages(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        Auth::setUser($me);

        $sentToMe = Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $me->uuid]);
        $sentByMe = Message::factory()->create(['user_uuid' => $me->uuid, 'recipient_user_uuid' => $other->uuid]);
        $broadcast = $this->service->send(['message' => 'Broadcast']);
        Auth::setUser($other);
        $notForMe = Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $other->uuid]);
        Auth::setUser($me);

        $result = $this->service->getInbox();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $uuids = $result->getCollection()->pluck('uuid')->all();
        $this->assertContains($sentToMe->uuid, $uuids);
        $this->assertContains($sentByMe->uuid, $uuids);
        $this->assertContains($broadcast->uuid, $uuids);
        $this->assertNotContains($notForMe->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testGetGroupMessagesReturnsOnlyMessagesFromThatGroup(): void
    {
        $sender = User::factory()->create();
        Auth::setUser($sender);
        $root = $this->service->send(['message' => 'Pierwsza']);
        $reply = $this->service->send(['message' => 'Druga', 'message_group_uuid' => $root->message_group_uuid]);
        $unrelated = Message::factory()->create(['user_uuid' => $sender->uuid]);

        $result = $this->service->getGroupMessages($root->group);

        $uuids = $result->getCollection()->pluck('uuid')->all();
        $this->assertContains($root->uuid, $uuids);
        $this->assertContains($reply->uuid, $uuids);
        $this->assertNotContains($unrelated->uuid, $uuids);
    }
}
