<?php

namespace App\Enums;

use App\Models\Calendar;
use Illuminate\Support\Carbon;

/**
 * Summary of VisitStatus
 */
enum VisitStatus: string
{
    case COMPLETED = 'COMPLETED';
    case NO_SHOW = 'NO_SHOW';
    case SCHEDULED = 'SCHEDULED';

    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::COMPLETED => 'Odbyta',
            self::NO_SHOW => 'Nieobecność',
            self::SCHEDULED => 'Zaplanowana',
        };
    }

    /**
     * @param Calendar $calendar
     * @return self
     */
    public static function fromCalendar(Calendar $calendar): self
    {
        if ($calendar->no_show) {
            return self::NO_SHOW;
        }

        return $calendar->startsAt()->lessThanOrEqualTo(Carbon::now()) ? self::COMPLETED : self::SCHEDULED;
    }
}
