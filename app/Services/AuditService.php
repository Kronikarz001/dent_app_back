<?php

namespace App\Services;

use App\Enums\AuditableEventType;
use App\Repositories\AuditRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of AuditService
 */
readonly class AuditService implements AuditServiceInterface
{
    /**
     * @param AuditRepositoryInterface $auditRepository
     */
    public function __construct(
        private AuditRepositoryInterface $auditRepository
    ) {}

    /**
     * @param Model $model
     * @return void
     */
    public function recordCreated(Model $model): void
    {
        $this->record($model, AuditableEventType::CREATE, null, $model->getAttributes());
    }

    /**
     * @param Model $model
     * @param array $from
     * @param array $to
     * @return void
     */
    public function recordUpdated(Model $model, array $from, array $to): void
    {
        $this->record($model, AuditableEventType::UPDATE, $from, $to);
    }

    /**
     * @param Model $model
     * @return void
     */
    public function recordDeleted(Model $model): void
    {
        $this->record($model, AuditableEventType::DELETE, $model->getAttributes(), null);
    }

    /**
     * @param Model $model
     * @param string $relation
     * @param array{attached: array, detached: array, updated: array} $syncResult
     * @return void
     */
    public function recordSync(Model $model, string $relation, array $syncResult): void
    {
        $attached = $syncResult['attached'] ?? [];
        $detached = $syncResult['detached'] ?? [];

        if (empty($attached) && empty($detached)) {
            return;
        }

        $this->record(
            $model,
            AuditableEventType::UPDATE,
            empty($detached) ? null : [$relation => $detached],
            empty($attached) ? null : [$relation => $attached]
        );
    }

    /**
     * @param Model $model
     * @param AuditableEventType $type
     * @param array|null $from
     * @param array|null $to
     * @return void
     */
    private function record(Model $model, AuditableEventType $type, ?array $from, ?array $to): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->auditRepository->create([
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'user_uuid' => Auth::id(),
            'type' => $type,
            'change_from' => $from === null ? null : $this->withoutHidden($model, $from),
            'change_to' => $to === null ? null : $this->withoutHidden($model, $to),
        ]);
    }

    /**
     * @param Model $model
     * @param array $attributes
     * @return array
     */
    private function withoutHidden(Model $model, array $attributes): array
    {
        return array_diff_key($attributes, array_flip($model->getHidden()));
    }
}
