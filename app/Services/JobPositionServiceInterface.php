<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of JobPositionServiceInterface
 */
interface JobPositionServiceInterface
{
    public function getJobPositions(): LengthAwarePaginator;

    public function getJobPositionsList(): LengthAwarePaginator;

    public function createJobPosition(array $data): JobPosition;

    public function updateJobPosition(JobPosition $jobPosition, array $data): JobPosition;

    public function deleteJobPosition(JobPosition $jobPosition): void;

    public function assignJobPosition(User $user, array $data): void;

    /**
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
