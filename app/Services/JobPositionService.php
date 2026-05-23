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
    public function __construct(
        private JobPositionRepositoryInterface $jobPositionRepository,
        private ExportServiceInterface $exportService,
    ) {}

    public function getJobPositions(): LengthAwarePaginator
    {
        return $this->jobPositionRepository->findAllWithPagination();
    }

    public function getJobPositionsList(): LengthAwarePaginator
    {
        return $this->jobPositionRepository->findAllWithPagination(['uuid', 'name']);
    }

    public function createJobPosition(array $data): JobPosition
    {
        return $this->jobPositionRepository->create($data);
    }

    public function updateJobPosition(JobPosition $jobPosition, array $data): JobPosition
    {
        return $this->jobPositionRepository->update($jobPosition, $data);
    }

    public function deleteJobPosition(JobPosition $jobPosition): void
    {
        $this->jobPositionRepository->delete($jobPosition);
    }

    public function assignJobPosition(User $user, array $data): void
    {
        collect($data)->each(function (array $jobPosition) use ($user) {
            $user->jobPositions()->syncWithoutDetaching($jobPosition['uuid']);
        });
    }

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new JobPositionExport($this->getJobPositions()->getCollection()), JobPosition::getModel());
    }
}
