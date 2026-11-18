<?php

namespace App\Enums;

/**
 * Summary of HoursType
 */
enum HoursType: string
{
    case PRACA = 'praca';
    case L4 = 'l4';
    case URLOP = 'urlop';

    /**
     * @param CalendarEventType $type
     * @return self|null
     */
    public static function fromCalendarEventType(CalendarEventType $type): ?self
    {
        return match ($type) {
            CalendarEventType::WORK => self::PRACA,
            CalendarEventType::SICK_LEAVE => self::L4,
            CalendarEventType::VACATION => self::URLOP,
            default => null,
        };
    }
}
