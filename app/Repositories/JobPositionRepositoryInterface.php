<?php
namespace App\Repositories;

use App\Models\JobPosition;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of JobPositionRepositoryInterface
 */
interface JobPositionRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return JobPosition|null
     */
    public function findByUuid(string $uuid): ?JobPosition;

    /**
     * @param array $data
     * @return JobPosition
     */
    public function create(array $data): JobPosition;

    /**
     * @param JobPosition|Model $jobPosition
     * @param array $data
     * @return JobPosition
     */
    public function update(JobPosition|Model $jobPosition, array $data): JobPosition;

    /**
     * @param JobPosition|Model $jobPosition
     * @return void
     */
    public function delete(JobPosition|Model $jobPosition): void;
}
