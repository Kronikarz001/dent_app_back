<?php

namespace Tests\Unit\Factories;

use App\Models\Calendar;
use Tests\Unit\UnitTestCase;

/**
 * Summary of CalendarFactoryTest
 */
final class CalendarFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCalendarCreateByFactory(): void
    {
        $calendar = Calendar::factory()->create(['name' => 'Wizyta kontrolna']);

        $this->assertEquals('Wizyta kontrolna', $calendar->name);
    }
}
