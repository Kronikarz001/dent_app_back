<?php

namespace App\Services;

use App\Exports\CalendarExport;
use App\Http\Requests\ExportRequest;
use App\Models\Calendar;
use App\Repositories\CalendarRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of CalendarService
 */
readonly class CalendarService implements CalendarServiceInterface
{
    /**
     * @param CalendarRepositoryInterface $calendarRepository
     * @param ExportServiceInterface $exportService
     */
    public function __construct(
        private CalendarRepositoryInterface $calendarRepository,
        private ExportServiceInterface $exportService,
    ) {}

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

    /**
     * @param Calendar $calendar
     * @param array $data
     * @return void
     */
    public function assignUsers(Calendar $calendar, array $data): void
    {
        $calendar->users()->sync($data['users'] ?? []);
        $calendar->patients()->sync($data['patients'] ?? []);
    }

    /**
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new CalendarExport($this->getCalendars()->getCollection()), Calendar::getModel());
    }
}
