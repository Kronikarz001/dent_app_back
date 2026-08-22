<?php

namespace App\Enums;

/**
 * Summary of CalendarEventType
 */
enum CalendarEventType: string
{
    case EXAMINATION = 'EXAMINATION';
    case WORK = 'WORK';
    case OTHER = 'OTHER';
    case SICK_LEAVE = 'SICK_LEAVE';
    case VACATION = 'VACATION';
    case ABSENCE = 'ABSENCE';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::EXAMINATION => 'Badanie',
            self::WORK => 'Praca',
            self::OTHER => 'Inne',
            self::SICK_LEAVE => 'L4',
            self::VACATION => 'Urlop',
            self::ABSENCE => 'Nieobecność',
        };
    }

    /**
     * @return bool
     */
    public function isEmployeeType(): bool
    {
        return in_array($this, self::employeeTypes(), true);
    }

    /**
     * @return bool
     */
    public function isAppointmentType(): bool
    {
        return in_array($this, self::appointmentTypes(), true);
    }

    /**
     * @return self[]
     */
    public static function appointmentTypes(): array
    {
        return [self::EXAMINATION, self::OTHER];
    }

    /**
     * @return self[]
     */
    public static function employeeTypes(): array
    {
        return [self::WORK, self::SICK_LEAVE, self::VACATION, self::ABSENCE];
    }
}
