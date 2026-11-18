<?php

namespace App\Repositories;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of AuditRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Audit>
 */
interface AuditRepositoryInterface extends SearchableRepositoryInterface
{
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
