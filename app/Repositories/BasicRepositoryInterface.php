<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of BasicRepositoryInterface
 */
interface BasicRepositoryInterface
{
    public function findAllWithPagination(array $params = []): LengthAwarePaginator;

    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?Model;

    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;
}
