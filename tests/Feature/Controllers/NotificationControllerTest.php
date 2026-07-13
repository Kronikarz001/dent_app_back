<?php

namespace Tests\Feature\Controllers;

use App\Models\Notification;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use Tests\TestCase;

/**
 * Summary of NotificationControllerTest
 */
class NotificationControllerTest extends TestCase
{
    /**
     * @param User $user
     * @param bool $read
     * @return Notification
     */
    private function createNotificationForUser(User $user, bool $read = false): Notification
    {
        return Notification::create([
            'type' => 'test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->uuid,
            'data' => [
                'title' => 'Tytuł',
                'message' => 'Treść',
                'type' => 'message_received',
            ],
            'read_at' => $read ? now() : null,
        ]);
    }

    /**
     * @return void
     */
    public function testIndexReturnsUserNotificationsWithUnreadCount(): void
    {
        $user = User::factory()->create();
        $this->createNotificationForUser($user);
        $this->createNotificationForUser($user, read: true);

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('notifications.index'));

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonCount(2, 'notifications');
        $response->assertJsonStructure([
            'notifications' => [['uuid', 'title', 'message', 'url', 'type', 'read', 'created_at']],
            'unread_count',
            'pagination',
        ]);
    }

    /**
     * @return void
     */
    public function testIndexDoesNotReturnOtherUsersNotifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->createNotificationForUser($other);

        $response = $this->callApiWithLoggedUser($user)
            ->getJson(route('notifications.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'notifications');
        $response->assertJsonPath('unread_count', 0);
    }

    /**
     * @return void
     */
    public function testMarkAsReadSingleNotification(): void
    {
        $user = User::factory()->create();
        $notification = $this->createNotificationForUser($user);
        $this->createNotificationForUser($user);

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('notifications.mark-as-read'), [
                'notification_uuid' => $notification->uuid,
            ]);

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * @return void
     */
    public function testMarkAsReadAllNotifications(): void
    {
        $user = User::factory()->create();
        $this->createNotificationForUser($user);
        $this->createNotificationForUser($user);

        $response = $this->callApiWithLoggedUser($user)
            ->postJson(route('notifications.mark-as-read'), []);

        $response->assertOk();
        $response->assertJsonPath('unread_count', 0);
    }

    /**
     * @return void
     */
    public function testGetPreferencesReturnsSeededGroups(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('notifications.preferences.index'));

        $response->assertOk();
        $this->assertNotEmpty($response->json('preferences'));
        $response->assertJsonStructure([
            'preferences' => [
                [
                    'group_id',
                    'group_code',
                    'group_name',
                    'notification_types' => [
                        ['type_id', 'type_code', 'type_name', 'channels' => [['channel_id', 'channel_name', 'enabled']]],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatePreferences(): void
    {
        $type = NotificationType::query()->firstOrFail();
        $channel = NotificationChannel::query()->where('is_configurable', true)->firstOrFail();

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('notifications.preferences.update'), [
                'preferences' => [
                    [
                        'type_id' => $type->uuid,
                        'channel_id' => $channel->uuid,
                        'enabled' => false,
                    ],
                ],
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('notification_preferences', [
            'notification_type_uuid' => $type->uuid,
            'notification_channel_uuid' => $channel->uuid,
            'enabled' => false,
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatePreferencesValidationFails(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('notifications.preferences.update'), [
                'preferences' => [
                    ['type_id' => 'not-a-uuid'],
                ],
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testEndpointsRequireAuthentication(): void
    {
        $this->getJson(route('notifications.index'))->assertUnauthorized();
    }
}
