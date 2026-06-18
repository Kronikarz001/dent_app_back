<?php

namespace App\Services;

use App\Http\Requests\ExportRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    /**
     * @param Model $model
     * @return LengthAwarePaginator
     */
    public function getHistory(Model $model): LengthAwarePaginator;

    /**
     * @param Model $model
     * @param ExportRequest $request
     * @return BinaryFileResponse
     *
     * @throws Exception
     * @throws WriterException
     */
    public function exportHistory(Model $model, ExportRequest $request): BinaryFileResponse;

    /**
     * @param string $resource
     * @param string $uuid
     * @return Model
     */
    public function resolveAuditable(string $resource, string $uuid): Model;
}
