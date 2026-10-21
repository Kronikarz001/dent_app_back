<?php

namespace App\Traits;

use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Summary of SerializesLocalDates
 */
trait SerializesLocalDates
{
    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return Carbon::instance($date)->jsonSerialize();
    }
}
