<?php

namespace Tests\Unit\Models;

use App\Models\NotificationGroup;
use App\Models\NotificationType;
use Tests\Unit\UnitTestCase;

/**
 * Summary of NotificationGroupTest
 */
final class NotificationGroupTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testNotificationTypesReturnsGroupsTypes(): void
    {
        $group = NotificationGroup::create([
            'code' => 'test-group',
            'display_name' => 'Test Group',
        ]);
        $type = NotificationType::create([
            'code' => 'test-type-group',
            'display_name' => 'Test Type',
            'notification_group_uuid' => $group->uuid,
        ]);

        $this->assertTrue($group->notificationTypes->contains($type));
    }
}
