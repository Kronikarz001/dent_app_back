<?php

namespace App\Services;

use App\Models\Calendar;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of CalendarServiceInterface
 */
interface CalendarServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getCalendars(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getCalendarsList(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Calendar
     */
    public function createCalendar(array $data): Calendar;

    /**
     * @param Calendar $calendar
     * @param array $data
     * @return Calendar
     */
    public function updateCalendar(Calendar $calendar, array $data): Calendar;

    /**
     * @param Calendar $calendar
     * @return void
     */
    public function deleteCalendar(Calendar $calendar): void;
}
