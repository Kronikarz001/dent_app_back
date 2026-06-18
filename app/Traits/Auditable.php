<?php

namespace App\Traits;

use App\Services\AuditServiceInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of Auditable
 */
trait Auditable
{
    /**
     * @return void
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            app(AuditServiceInterface::class)->recordCreated($model);
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            app(AuditServiceInterface::class)->recordUpdated(
                $model,
                array_intersect_key($model->getOriginal(), $changes),
                $changes
            );
        });

        static::deleted(function (Model $model) {
            app(AuditServiceInterface::class)->recordDeleted($model);
        });
    }
}
