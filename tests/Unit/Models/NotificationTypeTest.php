<?php

namespace Tests\Unit\Models;

use App\Models\Notification;
use App\Models\NotificationChannel;
use App\Models\NotificationGroup;
use App\Models\NotificationPreference;
use App\Models\NotificationType;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of NotificationTypeTest
 */
final class NotificationTypeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testNotificationGroupReturnsAssignedGroup(): void
    {
        $group = NotificationGroup::create([
            'code' => 'test-group-type',
            'display_name' => 'Test Group',
        ]);
        $type = NotificationType::create([
            'code' => 'test-type-with-group',
            'display_name' => 'Test Type',
            'notification_group_uuid' => $group->uuid,
        ]);

        $this->assertTrue($type->notificationGroup->is($group));
    }

    /**
     * @return void
     */
    public function testPreferencesReturnsTypesPreferences(): void
    {
        $type = NotificationType::create([
            'code' => 'test-type-preferences',
            'display_name' => 'Test Type',
        ]);
        $channel = NotificationChannel::create([
            'name' => 'test-channel-for-type',
            'display_name' => 'Test Channel',
        ]);
        $user = User::factory()->create();

        $preference = NotificationPreference::create([
            'user_uuid' => $user->uuid,
            'notification_type_uuid' => $type->uuid,
            'notification_channel_uuid' => $channel->uuid,
            'enabled' => true,
        ]);

        $this->assertTrue($type->preferences->contains($preference));
    }

    /**
     * @return void
     */
    public function testNotificationsReturnsNotificationsOfThisType(): void
    {
        $type = NotificationType::create([
            'code' => 'test-type-notifications',
            'display_name' => 'Test Type',
        ]);
        $user = User::factory()->create();

        $notification = Notification::create([
            'type' => 'App\\Notifications\\SomeNotification',
            'notification_type_uuid' => $type->uuid,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->uuid,
            'data' => ['foo' => 'bar'],
        ]);

        $this->assertTrue($type->notifications->contains($notification));
    }
}
