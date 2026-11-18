<?php

namespace Tests\Unit\Enums;

use App\Enums\CalendarEventType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of CalendarEventTypeTest
 */
final class CalendarEventTypeTest extends UnitTestCase
{
    /**
     * @return array<string, array{CalendarEventType, string, bool}>
     */
    public static function casesProvider(): array
    {
        return [
            'examination' => [CalendarEventType::EXAMINATION, 'Badanie', true],
            'other' => [CalendarEventType::OTHER, 'Inne', true],
            'work' => [CalendarEventType::WORK, 'Praca', false],
            'sick leave' => [CalendarEventType::SICK_LEAVE, 'L4', false],
            'vacation' => [CalendarEventType::VACATION, 'Urlop', false],
            'absence' => [CalendarEventType::ABSENCE, 'Nieobecność', false],
        ];
    }

    /**
     * @param CalendarEventType $type
     * @param string $name
     * @param bool $isAppointment
     * @return void
     */
    #[DataProvider('casesProvider')]
    public function testCaseHasNameAndBelongsToExactlyOneGroup(CalendarEventType $type, string $name, bool $isAppointment): void
    {
        $this->assertSame($name, $type->name());
        $this->assertSame($type->name, $type->value);
        $this->assertSame($isAppointment, $type->isAppointmentType());
        $this->assertSame(! $isAppointment, $type->isEmployeeType());
    }

    /**
     * @return void
     */
    public function testProviderCoversEveryCase(): void
    {
        $this->assertCount(count(CalendarEventType::cases()), self::casesProvider());
    }

    /**
     * @return void
     */
    public function testAppointmentAndEmployeeTypesPartitionAllCases(): void
    {
        $appointment = CalendarEventType::appointmentTypes();
        $employee = CalendarEventType::employeeTypes();

        $this->assertSame([CalendarEventType::EXAMINATION, CalendarEventType::OTHER], $appointment);
        $this->assertSame(
            [CalendarEventType::WORK, CalendarEventType::SICK_LEAVE, CalendarEventType::VACATION, CalendarEventType::ABSENCE],
            $employee
        );
        $this->assertEmpty(array_uintersect($appointment, $employee, fn ($a, $b) => strcmp($a->value, $b->value)));
        $this->assertCount(count(CalendarEventType::cases()), array_merge($appointment, $employee));
    }
}
