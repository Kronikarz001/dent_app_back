<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Summary of AuditServiceInterface
 */
interface AuditServiceInterface
{
    /**
     * @param Model $model
     * @return void
     */
    public function recordCreated(Model $model): void;

    /**
     * @param Model $model
     * @param array $from
     * @param array $to
     * @return void
     */
    public function recordUpdated(Model $model, array $from, array $to): void;

    /**
     * @param Model $model
     * @return void
     */
    public function recordDeleted(Model $model): void;

    /**
     * @param Model $model
     * @param string $relation
     * @param array{attached: array, detached: array, updated: array} $syncResult
     * @return void
     */
    public function recordSync(Model $model, string $relation, array $syncResult): void;
}
