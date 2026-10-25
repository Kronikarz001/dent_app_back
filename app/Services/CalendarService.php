<?php

namespace App\Services;

use App\Exceptions\ScheduleConflictException;
use App\Exports\CalendarExport;
use App\Http\Requests\ExportRequest;
use App\Models\Calendar;
use App\Repositories\CalendarRepositoryInterface;
use App\Repositories\ScheduleOverlapRepositoryInterface;
use App\Services\Concerns\DetectsScheduleConflicts;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of CalendarService
 */
readonly class CalendarService implements CalendarServiceInterface
{
    use DetectsScheduleConflicts;

    /**
     * @param CalendarRepositoryInterface $calendarRepository
     * @param ExportServiceInterface $exportService
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private CalendarRepositoryInterface $calendarRepository,
        private ExportServiceInterface $exportService,
        private AuditServiceInterface $auditService,
    ) {}

    /**
     * @return ScheduleOverlapRepositoryInterface
     */
    protected function scheduleRepository(): ScheduleOverlapRepositoryInterface
    {
        return $this->calendarRepository;
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
        return $this->calendarRepository->findSelectAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return Calendar
     */
    public function createCalendar(array $data): Calendar
    {
        if (! empty($data['no_show'])) {
            $data['is_active'] = false;
        }

        $calendar = $this->calendarRepository->create($data);

        if (array_key_exists('dental_examinations', $data)) {
            $this->auditService->recordSync($calendar, 'dental_examinations', $this->calendarRepository->syncRelation($calendar, 'dentalExaminations', $data['dental_examinations']));
        }

        return $calendar;
    }

    /**
     * @param Calendar $calendar
     * @param array $data
     * @return Calendar
     */
    public function updateCalendar(Calendar $calendar, array $data): Calendar
    {
        if (! empty($data['no_show'])) {
            $data['is_active'] = false;
        }

        $calendar = $this->calendarRepository->update($calendar, $data);

        if (array_key_exists('dental_examinations', $data)) {
            $this->auditService->recordSync($calendar, 'dental_examinations', $this->calendarRepository->syncRelation($calendar, 'dentalExaminations', $data['dental_examinations']));
        }

        return $calendar;
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
     *
     * @throws ScheduleConflictException
     */
    public function assignUsers(Calendar $calendar, array $data): void
    {
        $this->assertNoScheduleConflicts($calendar, $data['users'] ?? [], $data['patients'] ?? []);

        $this->auditService->recordSync($calendar, 'users', $this->calendarRepository->syncRelation($calendar, 'users', $data['users'] ?? []));
        $this->auditService->recordSync($calendar, 'patients', $this->calendarRepository->syncRelation($calendar, 'patients', $data['patients'] ?? []));
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
        return $this->exportService->export($request, new CalendarExport($this->calendarRepository->findAllWithPagination(perPage: -1)->getCollection()), Calendar::getModel());
    }
}
