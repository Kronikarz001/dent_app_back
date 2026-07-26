<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of CompanyRepositoryInterface
 */
interface CompanyRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Company|null
     */
    public function findByUuid(string $uuid): ?Company;

    /**
     * @param array $data
     * @return Company
     */
    public function create(array $data): Company;

    /**
     * @param Company|Model $model
     * @param array $data
     * @return Company
     */
    public function update(Company|Model $model, array $data): Company;

    /**
     * @param Model|Company $model
     * @return bool
     */
    public function delete(Model|Company $model): bool;
}
