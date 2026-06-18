<?php

namespace App\Services;

use App\Enums\AuditableEventType;
use App\Enums\AuditableType;
use App\Exceptions\InvalidAuditableIdentifierException;
use App\Exports\AuditExport;
use App\Http\Requests\ExportRequest;
use App\Models\Audit;
use App\Repositories\AuditRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Summary of AuditService
 */
readonly class AuditService implements AuditServiceInterface
{
    /**
     * @param AuditRepositoryInterface $auditRepository
     * @param ExportServiceInterface $exportService
     */
    public function __construct(
        private AuditRepositoryInterface $auditRepository,
        private ExportServiceInterface $exportService,
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

    /**
     * @param Model $model
     * @return LengthAwarePaginator
     */
    public function getHistory(Model $model): LengthAwarePaginator
    {
        return $this->auditRepository->findAllWithPagination([
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
        ]);
    }

    /**
     * @param Model $model
     * @param ExportRequest $request
     * @return BinaryFileResponse
     * @throws Exception
     * @throws WriterException
     */
    public function exportHistory(Model $model, ExportRequest $request): BinaryFileResponse
    {
        return $this->exportService->export($request, new AuditExport($this->getHistory($model)->getCollection()), Audit::getModel());
    }

    /**
     * @param string $resource
     * @param string $uuid
     * @return Model
     */
    public function resolveAuditable(string $resource, string $uuid): Model
    {
        $type = AuditableType::tryFrom($resource);

        if ($type === null) {
            throw new InvalidAuditableIdentifierException("Unknown auditable resource [{$resource}].");
        }

        return $this->auditRepository->findAuditableOrFail($type->modelClass(), $uuid);
    }
}
