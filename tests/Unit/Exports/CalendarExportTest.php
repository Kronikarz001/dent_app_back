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
            'Data',
            'Data zakończenia',
            'Godzina rozpoczęcia',
            'Godzina zakończenia',
            'Nie przyszedł',
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
            'date' => '2026-01-01',
            'end_date' => '2026-01-02',
            'start_time' => '10:00',
            'end_time' => '10:30',
            'no_show' => false,
            'is_active' => true,
        ]);

        $export = new CalendarExport(collect());
        $result = $export->map($calendar);

        $this->assertEquals([
            'name' => 'Wizyta',
            'description' => 'Opis wizyty',
            'type' => 'EXAMINATION',
            'date' => $calendar->date,
            'end_date' => $calendar->end_date,
            'start_time' => $calendar->start_time,
            'end_time' => $calendar->end_time,
            'no_show' => 'Nie',
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
