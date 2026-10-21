<?php

namespace App\Repositories;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of AuditRepositoryInterface
 */
interface AuditRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param array $data
     * @return Audit
     */
    public function create(array $data): Audit;

    /**
     * @param string $modelClass
     * @param string $uuid
     * @return Model
     */
    public function findAuditableOrFail(string $modelClass, string $uuid): Model;

    /**
     * @param Model $model
     * @param string $attribute
     * @param string $value
     * @return Audit|null
     */
    public function findLatestChangeTo(Model $model, string $attribute, string $value): ?Audit;
}
