<?php

namespace App\Services;

use App\Models\JobPosition;
use App\Models\User;
use App\Repositories\JobPositionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Summary of JobPositionService
 */
readonly class JobPositionService implements JobPositionServiceInterface
{
    /**
     * @param JobPositionRepositoryInterface $jobPositionRepository
     */
    public function __construct(
        private JobPositionRepositoryInterface $jobPositionRepository
    )
    {
    }

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
        collect($data)->each(function (array $jobPosition) use ($user) {
           $user->jobPositions()->syncWithoutDetaching($jobPosition['uuid']);
        });
    }
}
