<?php

namespace App\Services;

use App\Models\Calendar;
use App\Repositories\CalendarRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of CalendarService
 */
readonly class CalendarService implements CalendarServiceInterface
{
    /**
     * @param CalendarRepositoryInterface $calendarRepository
     */
    public function __construct(
        private CalendarRepositoryInterface $calendarRepository,
    )
    {
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getCalendars(): LengthAwarePaginator
    {
        return $this->calendarRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getCalendarsList(): LengthAwarePaginator
    {
        return $this->calendarRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Calendar
     */
    public function createCalendar(array $data): Calendar
    {
        return $this->calendarRepository->create($data);
    }

    /**
     * @param Calendar $calendar
     * @param array $data
     * @return Calendar
     */
    public function updateCalendar(Calendar $calendar, array $data): Calendar
    {
        return $this->calendarRepository->update($calendar, $data);
    }

    /**
     * @param Calendar $calendar
     * @return void
     */
    public function deleteCalendar(Calendar $calendar): void
    {
        $this->calendarRepository->delete($calendar);
    }
}
