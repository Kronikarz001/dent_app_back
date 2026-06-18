<?php

namespace App\Repositories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of MaterialRepositoryInterface
 */
interface MaterialRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Material|null
     */
    public function findByUuid(string $uuid): ?Material;

    /**
     * @param array $data
     * @return Material
     */
    public function create(array $data): Material;

    /**
     * @param Material|Model $model
     * @param array $data
     * @return Material
     */
    public function update(Material|Model $model, array $data): Material;

    /**
     * @param Model|Material $model
     * @return bool
     */
    public function delete(Model|Material $model): bool;
}
