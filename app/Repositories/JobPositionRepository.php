<?php

namespace App\Repositories;

use App\Models\JobPosition;
use App\Search\JobPositionSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of JobPositionRepository
 */
readonly class JobPositionRepository implements JobPositionRepositoryInterface
{
    /**
     * @param JobPositionSearch $search
     */
    public function __construct(
        private JobPositionSearch $search
    ) {
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param string $uuid
     * @return JobPosition|null
     */
    public function findByUuid(string $uuid): ?JobPosition
    {
        return JobPosition::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return JobPosition
     */
    public function create(array $data): JobPosition
    {
        return JobPosition::create($data);
    }

    /**
     * @param JobPosition|Model $jobPosition
     * @param array $data
     * @return JobPosition
     */
    public function update(JobPosition|Model $jobPosition, array $data): JobPosition
    {
        $jobPosition->update($data);
        return $jobPosition->fresh();
    }

    /**
     * @param JobPosition|Model $jobPosition
     * @return void
     */
    public function delete(JobPosition|Model $jobPosition): void
    {
        $jobPosition->delete();
    }
}
