<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of NotificationTest
 */
final class NotificationTest extends UnitTestCase
{
    /**
     * @param array $overrides
     * @return Notification
     */
    private function makeNotification(array $overrides = []): Notification
    {
        $user = User::factory()->create();

        return Notification::create(array_merge([
            'type' => 'App\\Notifications\\SomeNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->uuid,
            'data' => ['foo' => 'bar'],
        ], $overrides));
    }

    /**
     * @return void
     */
    public function testDataIsCastToArray(): void
    {
        $notification = $this->makeNotification(['data' => ['foo' => 'bar']]);

        $this->assertSame(['foo' => 'bar'], $notification->data);
    }

    /**
     * @return void
     */
    public function testNotifiableReturnsOwningModel(): void
    {
        $user = User::factory()->create();
        $notification = $this->makeNotification(['notifiable_type' => User::class, 'notifiable_id' => $user->uuid]);

        $this->assertTrue($notification->notifiable->is($user));
    }

    /**
     * @return void
     */
    public function testTypeRelationMethodReturnsAssignedNotificationType(): void
    {
        $type = NotificationType::create([
            'code' => 'test-type-notification-model',
            'display_name' => 'Test Type',
        ]);
        $notification = $this->makeNotification(['notification_type_uuid' => $type->uuid]);

        $this->assertTrue($notification->type()->first()->is($type));
    }

    /**
     * @return void
     */
    public function testMarkAsReadSetsReadAt(): void
    {
        $notification = $this->makeNotification();
        $this->assertNull($notification->read_at);

        $notification->markAsRead();

        $this->assertNotNull($notification->refresh()->read_at);
    }

    /**
     * @return void
     */
    public function testMarkAsUnreadClearsReadAt(): void
    {
        $notification = $this->makeNotification();
        $notification->markAsRead();

        $notification->markAsUnread();

        $this->assertNull($notification->refresh()->read_at);
    }

    /**
     * @return void
     */
    public function testScopeReadOnlyReturnsReadNotifications(): void
    {
        $read = $this->makeNotification();
        $read->markAsRead();
        $unread = $this->makeNotification();

        $results = Notification::read()->get();

        $this->assertTrue($results->contains($read));
        $this->assertFalse($results->contains($unread));
    }

    /**
     * @return void
     */
    public function testScopeUnreadOnlyReturnsUnreadNotifications(): void
    {
        $read = $this->makeNotification();
        $read->markAsRead();
        $unread = $this->makeNotification();

        $results = Notification::unread()->get();

        $this->assertTrue($results->contains($unread));
        $this->assertFalse($results->contains($read));
    }
}
