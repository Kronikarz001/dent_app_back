<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of BasicRepositoryInterface
 */
interface BasicRepositoryInterface
{
    /**
     * @param  array  $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator;

    /**
     * @param  array  $columns
     * @param  array  $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator;

    /**
     * @param  string  $uuid
     * @return Model|null
     */
    public function findByUuid(string $uuid): ?Model;

    /**
     * @param  array  $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * @param  Model  $model
     * @param  array  $data
     * @return Model
     */
    public function update(Model $model, array $data): Model;

    /**
     * @param  Model  $model
     * @return bool
     */
    public function delete(Model $model): bool;
}
