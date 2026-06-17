<?php

namespace Tests\Unit\Exports;

use App\Exports\CalendarExport;
use App\Models\Calendar;
use Tests\Unit\UnitTestCase;

/**
 * Summary of CalendarExportTest
 */
final class CalendarExportTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testHeadingsAreCorrect(): void
    {
        $export = new CalendarExport(collect());
        $expected = [
            'Nazwa',
            'Opis',
            'Typ',
            'Data początku',
            'Data końca',
            'Aktywny',
        ];
        $this->assertEquals($expected, $export->headings());
    }

    /**
     * @return void
     */
    public function testMapWithActiveCalendarReturnsExpectedArray(): void
    {
        $calendar = new Calendar([
            'name' => 'Wizyta',
            'description' => 'Opis wizyty',
            'type' => 'EXAMINATION',
            'start_date' => '2026-01-01 10:00:00',
            'end_date' => '2026-01-02 10:00:00',
            'is_active' => true,
        ]);

        $export = new CalendarExport(collect());
        $result = $export->map($calendar);

        $this->assertEquals([
            'name' => 'Wizyta',
            'description' => 'Opis wizyty',
            'type' => 'EXAMINATION',
            'start_date' => $calendar->start_date,
            'end_date' => $calendar->end_date,
            'is_active' => 'Tak',
        ], $result);
    }

    /**
     * @return void
     */
    public function testMapWithInactiveCalendarReturnsNieForIsActive(): void
    {
        $calendar = new Calendar(['name' => 'Wizyta', 'is_active' => false]);

        $export = new CalendarExport(collect());
        $result = $export->map($calendar);

        $this->assertSame('Nie', $result['is_active']);
    }

    /**
     * @return void
     */
    public function testCollectionReturnsCalendarsCollection(): void
    {
        $calendar1 = new Calendar(['name' => 'A']);
        $calendar2 = new Calendar(['name' => 'B']);
        $calendars = collect([$calendar1, $calendar2]);
        $export = new CalendarExport($calendars);

        $collection = $export->collection();
        $this->assertSame($calendars, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame($calendar1, $collection[0]);
        $this->assertSame($calendar2, $collection[1]);
    }
}
