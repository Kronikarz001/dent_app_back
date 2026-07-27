<?php

namespace Tests\Feature\Services;

use App\Exceptions\DefaultMessageGroupException;
use App\Exceptions\MessageGroupAccessDeniedException;
use App\Exceptions\MessageGroupMemberNotFoundException;
use App\Exceptions\MessageGroupMinimumMembersException;
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

    /**
     * @return void
     */
    public function testGetGroupThrowsWhenUserIsNotMember(): void
    {
        $outsider = User::factory()->create();
        $group = MessageGroup::factory()->create();
        Auth::setUser($outsider);

        $this->expectException(MessageGroupAccessDeniedException::class);

        $this->groupService->getGroup($group);
    }

    /**
     * @return void
     */
    public function testUpdateThrowsWhenUserIsNotMember(): void
    {
        $outsider = User::factory()->create();
        $group = MessageGroup::factory()->create();
        Auth::setUser($outsider);

        $this->expectException(MessageGroupAccessDeniedException::class);

        $this->groupService->update($group, ['name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testUpdateThrowsWhenGroupIsDefault(): void
    {
        $member = User::factory()->create();
        $group = MessageGroup::factory()->create(['is_default' => true]);
        $group->users()->attach($member->uuid);
        Auth::setUser($member);

        $this->expectException(DefaultMessageGroupException::class);

        $this->groupService->update($group, ['name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testRemoveUserThrowsWhenTargetIsNotMember(): void
    {
        $member = User::factory()->create();
        $notMember = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach([$member->uuid, User::factory()->create()->uuid]);
        Auth::setUser($member);

        $this->expectException(MessageGroupMemberNotFoundException::class);

        $this->groupService->removeUser($group, $notMember);
    }

    /**
     * @return void
     */
    public function testRemoveUserThrowsWhenGroupWouldHaveTooFewMembers(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach([$member->uuid, $other->uuid]);
        Auth::setUser($member);

        $this->expectException(MessageGroupMinimumMembersException::class);

        $this->groupService->removeUser($group, $other);
    }

    /**
     * @return void
     */
    public function testRemoveUserDetachesWhenGroupStaysAboveMinimum(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $third = User::factory()->create();
        $group = MessageGroup::factory()->create();
        $group->users()->attach([$member->uuid, $other->uuid, $third->uuid]);
        Auth::setUser($member);

        $this->groupService->removeUser($group, $other);

        $this->assertFalse($group->users()->where('users.uuid', $other->uuid)->exists());
    }

    /**
     * @return void
     */
    public function testAddUserThrowsWhenRequesterIsNotMember(): void
    {
        $outsider = User::factory()->create();
        $newUser = User::factory()->create();
        $group = MessageGroup::factory()->create();
        Auth::setUser($outsider);

        $this->expectException(MessageGroupAccessDeniedException::class);

        $this->groupService->addUser($group, $newUser);
    }
}
