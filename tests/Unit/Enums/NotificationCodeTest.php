<?php

namespace Tests\Unit\Enums;

use App\Enums\NotificationCode;
use Tests\Unit\UnitTestCase;

/**
 * Summary of NotificationCodeTest
 */
final class NotificationCodeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testValuesAreLowercaseNamesOfCases(): void
    {
        foreach (NotificationCode::cases() as $code) {
            $this->assertSame(strtolower($code->name), $code->value);
        }
    }

    /**
     * @return void
     */
    public function testValuesAreStable(): void
    {
        $this->assertSame([
            'message_received',
            'announcement_published',
            'calendar_event_created',
            'calendar_event_updated',
            'calendar_event_reminder',
            'patient_created',
            'dental_examination_created',
        ], array_column(NotificationCode::cases(), 'value'));
    }
}
