<?php

namespace Tests\Unit\Models;

use App\Models\NotificationChannel;
use App\Models\NotificationPreference;
use App\Models\NotificationType;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of NotificationChannelTest
 */
final class NotificationChannelTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testPreferencesReturnsChannelsPreferences(): void
    {
        $channel = NotificationChannel::create([
            'name' => 'test-channel',
            'display_name' => 'Test Channel',
            'is_configurable' => true,
            'is_internal' => false,
        ]);
        $type = NotificationType::create([
            'code' => 'test-type-channel',
            'display_name' => 'Test Type',
        ]);
        $user = User::factory()->create();

        $preference = NotificationPreference::create([
            'user_uuid' => $user->uuid,
            'notification_type_uuid' => $type->uuid,
            'notification_channel_uuid' => $channel->uuid,
            'enabled' => true,
        ]);

        $this->assertTrue($channel->preferences->contains($preference));
    }

    /**
     * @return void
     */
    public function testIsConfigurableAndIsInternalAreCastToBoolean(): void
    {
        $channel = NotificationChannel::create([
            'name' => 'test-channel-cast',
            'display_name' => 'Test Channel',
            'is_configurable' => 1,
            'is_internal' => 0,
        ]);

        $this->assertTrue($channel->is_configurable);
        $this->assertFalse($channel->is_internal);
    }
}
