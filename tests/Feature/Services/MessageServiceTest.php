<?php

namespace Tests\Feature\Services;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\User;
use App\Services\MessageServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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
    public function testSendDispatchesMessageSentEvent(): void
    {
        Event::fake([MessageSent::class]);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        Auth::setUser($sender);

        $message = $this->service->send(['message' => 'Hej', 'recipient_uuid' => $recipient->uuid]);

        Event::assertDispatched(MessageSent::class, fn (MessageSent $event) => $event->message->uuid === $message->uuid);
    }

    /**
     * @return void
     */
    public function testSendWithoutRecipientCreatesGroupAndLinksRootMessage(): void
    {
        $sender = User::factory()->create();
        Auth::setUser($sender);

        $message = $this->service->send(['message' => 'Do wszystkich']);

        $defaultGroup = MessageGroup::where('is_default', true)->first();

        $this->assertNotNull($message->message_group_uuid);
        $this->assertSame($defaultGroup->uuid, $message->message_group_uuid);
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
    public function testGetAllMessageForUserIncludesPrivateAndGroupMessages(): void
    {
        $target = User::factory()->create();
        $other = User::factory()->create();
        Auth::setUser($target);
        $received = Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $target->uuid]);
        $broadcast = $this->service->send(['message' => 'Broadcast']);
        $notForTarget = Message::factory()->create(['user_uuid' => $other->uuid, 'recipient_user_uuid' => $other->uuid]);

        $result = $this->service->getAllMessageForUser($target);

        $uuids = $result->getCollection()->pluck('uuid')->all();
        $this->assertContains($received->uuid, $uuids);
        $this->assertContains($broadcast->uuid, $uuids);
        $this->assertNotContains($notForTarget->uuid, $uuids);
    }

    /**
     * @return void
     */
    public function testDeleteMessageRemovesFromDatabase(): void
    {
        $message = Message::factory()->create();

        $this->service->deleteMessage($message);

        $this->assertDatabaseMissing(self::MESSAGES_TABLE, ['uuid' => $message->uuid]);
    }

    /**
     * @return void
     */
    public function testMarkAsReadSetsIsReadWhenUserIsRecipient(): void
    {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();
        Auth::setUser($recipient);
        $message = Message::factory()->create(['user_uuid' => $sender->uuid, 'recipient_user_uuid' => $recipient->uuid]);

        $this->service->markAsRead($message);

        $this->assertDatabaseHas('message_reads', ['message_uuid' => $message->uuid, 'user_uuid' => $recipient->uuid]);
    }

    /**
     * @return void
     */
    public function testMessageUnreadAttributeReflectsReadStatusForCurrentUser(): void
    {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();
        Auth::setUser($recipient);
        $message = Message::factory()->create(['user_uuid' => $sender->uuid, 'recipient_user_uuid' => $recipient->uuid]);

        $this->assertTrue($message->fresh()->unread);

        $this->service->markAsRead($message);

        $this->assertFalse($message->fresh()->unread);
    }

    /**
     * @return void
     */
    public function testGetUnreadConversationsCountCountsOnlyUnreadConversations(): void
    {
        $me = User::factory()->create();
        $otherUnread = User::factory()->create();
        $otherRead = User::factory()->create();
        Auth::setUser($me);

        Message::factory()->create(['user_uuid' => $otherUnread->uuid, 'recipient_user_uuid' => $me->uuid]);
        $readMessage = Message::factory()->create(['user_uuid' => $otherRead->uuid, 'recipient_user_uuid' => $me->uuid]);
        $readMessage->readBy()->attach($me->uuid);

        $count = $this->service->getUnreadConversationsCount();

        $this->assertSame(1, $count);
    }
}
