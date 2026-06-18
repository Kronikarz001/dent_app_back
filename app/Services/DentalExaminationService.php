<?php

namespace App\Services;

use App\Exports\DentalExaminationExport;
use App\Http\Requests\ExportRequest;
use App\Models\DentalExamination;
use App\Repositories\DentalExaminationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of DentalExaminationService
 */
readonly class DentalExaminationService implements DentalExaminationServiceInterface
{
    /**
     * @param DentalExaminationRepositoryInterface $dentalExaminationRepository
     * @param ExportServiceInterface $exportService
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private DentalExaminationRepositoryInterface $dentalExaminationRepository,
        private ExportServiceInterface $exportService,
        private AuditServiceInterface $auditService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getDentalExaminations(): LengthAwarePaginator
    {
        return $this->dentalExaminationRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getDentalExaminationsList(): LengthAwarePaginator
    {
        return $this->dentalExaminationRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return DentalExamination
     */
    public function createDentalExamination(array $data): DentalExamination
    {
        $dentalExamination = $this->dentalExaminationRepository->create($data);

        if (array_key_exists('materials', $data)) {
            $this->auditService->recordSync($dentalExamination, 'materials', $dentalExamination->materials()->sync($data['materials']));
        }

        if (array_key_exists('calendars', $data)) {
            $this->auditService->recordSync($dentalExamination, 'calendars', $dentalExamination->calendars()->sync($data['calendars']));
        }

        return $dentalExamination;
    }

    /**
     * @param DentalExamination $dentalExamination
     * @param array $data
     * @return DentalExamination
     */
    public function updateDentalExamination(DentalExamination $dentalExamination, array $data): DentalExamination
    {
        $dentalExamination = $this->dentalExaminationRepository->update($dentalExamination, $data);

        if (array_key_exists('materials', $data)) {
            $this->auditService->recordSync($dentalExamination, 'materials', $dentalExamination->materials()->sync($data['materials']));
        }

        if (array_key_exists('calendars', $data)) {
            $this->auditService->recordSync($dentalExamination, 'calendars', $dentalExamination->calendars()->sync($data['calendars']));
        }

        return $dentalExamination;
    }

    /**
     * @param DentalExamination $dentalExamination
     * @return void
     */
    public function deleteDentalExamination(DentalExamination $dentalExamination): void
    {
        $this->dentalExaminationRepository->delete($dentalExamination);
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
        return $this->exportService->export($request, new DentalExaminationExport($this->getDentalExaminations()->getCollection()), DentalExamination::getModel());
    }
}
