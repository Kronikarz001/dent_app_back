<?php

namespace Tests\Unit\Models;

use App\Models\NotificationChannel;
use App\Models\NotificationPreference;
use App\Models\NotificationType;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of NotificationPreferenceTest
 */
final class NotificationPreferenceTest extends UnitTestCase
{
    /**
     * @param array $overrides
     * @return NotificationPreference
     */
    private function makePreference(array $overrides = []): NotificationPreference
    {
        $user = User::factory()->create();
        $type = NotificationType::create([
            'code' => 'test-type-'.uniqid(),
            'display_name' => 'Test Type',
        ]);
        $channel = NotificationChannel::create([
            'name' => 'test-channel-'.uniqid(),
            'display_name' => 'Test Channel',
        ]);

        return NotificationPreference::create(array_merge([
            'user_uuid' => $user->uuid,
            'notification_type_uuid' => $type->uuid,
            'notification_channel_uuid' => $channel->uuid,
            'enabled' => true,
        ], $overrides));
    }

    /**
     * @return void
     */
    public function testEnabledIsCastToBoolean(): void
    {
        $preference = $this->makePreference(['enabled' => 0]);

        $this->assertFalse($preference->enabled);
    }

    /**
     * @return void
     */
    public function testUserReturnsOwningUser(): void
    {
        $preference = $this->makePreference();

        $this->assertInstanceOf(User::class, $preference->user);
    }

    /**
     * @return void
     */
    public function testChannelAndTypeReturnRelatedModels(): void
    {
        $preference = $this->makePreference();

        $this->assertInstanceOf(NotificationChannel::class, $preference->channel);
        $this->assertInstanceOf(NotificationType::class, $preference->type);
    }
}
