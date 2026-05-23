<?php

namespace App\Repositories;

use App\Models\JobPosition;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of JobPositionRepositoryInterface
 */
interface JobPositionRepositoryInterface extends BasicRepositoryInterface
{
    public function findByUuid(string $uuid): ?JobPosition;

    public function create(array $data): JobPosition;

    public function update(JobPosition|Model $model, array $data): JobPosition;

    public function delete(Model|JobPosition $model): bool;
}
