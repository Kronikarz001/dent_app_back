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

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::EXAMINATION => 'Badanie',
            self::WORK => 'Praca',
            self::OTHER => 'Inne',
        };
    }
}
