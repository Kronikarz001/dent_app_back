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
    /**
     * @return LengthAwarePaginator
     */
    public function getJobPositions(): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator
     */
    public function getJobPositionsList(): LengthAwarePaginator;

    /**
     * @param  array  $data
     * @return JobPosition
     */
    public function createJobPosition(array $data): JobPosition;

    /**
     * @param  JobPosition  $jobPosition
     * @param  array  $data
     * @return JobPosition
     */
    public function updateJobPosition(JobPosition $jobPosition, array $data): JobPosition;

    /**
     * @param  JobPosition  $jobPosition
     * @return void
     */
    public function deleteJobPosition(JobPosition $jobPosition): void;

    /**
     * @param  User  $user
     * @param  array  $data
     * @return void
     */
    public function assignJobPosition(User $user, array $data): void;

    /**
     * @param  ExportRequest  $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function export(ExportRequest $request): BinaryFileResponse;
}
