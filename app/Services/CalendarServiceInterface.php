<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of CalendarServiceInterface
 */
interface CalendarServiceInterface
{
    /**
     * @param array{user_uuids?: string[], date_from?: string, date_to?: string} $filters
     * @return LengthAwarePaginator
     */
    public function getCalendars(array $filters = []): LengthAwarePaginator;

    /**
     * @return Collection
     */
    public function getTodayCalendars(): Collection;

    /**
     * @param Calendar $calendar
     * @return Calendar
     */
    public function getCalendarDetails(Calendar $calendar): Calendar;

    /**
     * @return Collection
     */
    public function getCalendarsList(): Collection;

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

    /**
     * @param Calendar $calendar
     * @param array $data
     * @return void
     */
    public function assignUsers(Calendar $calendar, array $data): void;

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
