<?php

namespace Tests\Feature\Notifications;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\GenericNotification;
use Tests\TestCase;

/**
 * Summary of DatabaseChannelTest
 */
class DatabaseChannelTest extends TestCase
{
    /**
     * @return void
     */
    public function testNotifyStoresNotificationWithUuid(): void
    {
        $user = User::factory()->create();

        $user->notify(new GenericNotification([
            'title' => 'Tytuł',
            'message' => 'Treść',
            'type' => 'message_received',
        ]));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->uuid,
        ]);

        $notification = Notification::query()->firstOrFail();

        $this->assertNotNull($notification->uuid);
        $this->assertSame('Tytuł', $notification->data['title']);
        $this->assertSame('message_received', $notification->type);
        $this->assertNull($notification->read_at);
    }

    /**
     * @return void
     */
    public function testDeliveredNotificationIsVisibleThroughApi(): void
    {
        $user = User::factory()->create();

        $user->notify(new GenericNotification([
            'title' => 'Tytuł',
            'message' => 'Treść',
            'type' => 'message_received',
        ]));

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('notifications.index'));

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonCount(1, 'notifications');
    }

    /**
     * @return void
     */
    public function testUnreadRelationAndMarkAsRead(): void
    {
        $user = User::factory()->create();

        $user->notify(new GenericNotification(['title' => 'T', 'type' => 'message_received']));

        $this->assertCount(1, $user->unreadNotifications()->get());

        $user->unreadNotifications()->first()->markAsRead();

        $this->assertCount(0, $user->unreadNotifications()->get());
        $this->assertCount(1, $user->readNotifications()->get());
    }
}
