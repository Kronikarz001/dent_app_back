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
     * @param JobPosition|Model $model
     * @param array $data
     * @return JobPosition
     */
    public function update(JobPosition|Model $model, array $data): JobPosition;

    /**
     * @param Model|JobPosition $model
     * @return bool
     */
    public function delete(Model|JobPosition $model): bool;
}
