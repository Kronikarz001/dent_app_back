<?php

namespace App\Services;

use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * @param array $data
     * @return JobPosition
     */
    public function createJobPosition(array $data): JobPosition;

    /**
     * @param JobPosition $jobPosition
     * @param array $data
     * @return JobPosition
     */
    public function updateJobPosition(JobPosition $jobPosition, array $data): JobPosition;

    /**
     * @param JobPosition $jobPosition
     * @return void
     */
    public function deleteJobPosition(JobPosition $jobPosition): void;

    /**
     * @param User $user
     * @param array $data
     * @return void
     */
    public function assignJobPosition(User $user, array $data): void;
}
