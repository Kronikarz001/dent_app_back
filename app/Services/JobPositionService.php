<?php

namespace App\Services;

use App\Exports\JobPositionExport;
use App\Http\Requests\ExportRequest;
use App\Models\JobPosition;
use App\Models\User;
use App\Repositories\JobPositionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of JobPositionService
 */
readonly class JobPositionService implements JobPositionServiceInterface
{
    /**
     * @param JobPositionRepositoryInterface $jobPositionRepository
     * @param ExportServiceInterface $exportService
     * @param AuditServiceInterface $auditService
     */
    public function __construct(
        private JobPositionRepositoryInterface $jobPositionRepository,
        private ExportServiceInterface $exportService,
        private AuditServiceInterface $auditService,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getJobPositions(): LengthAwarePaginator
    {
        return $this->jobPositionRepository->findAllWithPagination();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getJobPositionsList(): LengthAwarePaginator
    {
        return $this->jobPositionRepository->findAllWithPagination(['uuid', 'name']);
    }

    /**
     * @param array $data
     * @return JobPosition
     */
    public function createJobPosition(array $data): JobPosition
    {
        return $this->jobPositionRepository->create($data);
    }

    /**
     * @param JobPosition $jobPosition
     * @param array $data
     * @return JobPosition
     */
    public function updateJobPosition(JobPosition $jobPosition, array $data): JobPosition
    {
        return $this->jobPositionRepository->update($jobPosition, $data);
    }

    /**
     * @param JobPosition $jobPosition
     * @return void
     */
    public function deleteJobPosition(JobPosition $jobPosition): void
    {
        $this->jobPositionRepository->delete($jobPosition);
    }

    /**
     * @param User $user
     * @param array $data
     * @return void
     */
    public function assignJobPosition(User $user, array $data): void
    {
        $this->auditService->recordSync($user, 'job_positions', $user->jobPositions()->syncWithoutDetaching($data['job_positions']));
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
        return $this->exportService->export($request, new JobPositionExport($this->getJobPositions()->getCollection()), JobPosition::getModel());
    }
}
