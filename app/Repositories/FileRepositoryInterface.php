<?php

namespace App\Repositories;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of FileRepositoryInterface
 */
interface FileRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param array $data
     * @return File
     */
    public function create(array $data): File;

    /**
     * @param string $uuid
     * @return File
     */
    public function findByUuid(string $uuid): File;

    /**
     * @param Model|File $model
     * @param array $data
     * @return File
     */
    public function update(Model|File $model, array $data): File;

    /**
     * @param File|Model $model
     * @return bool
     */
    public function delete(File|Model $model): bool;

    /**
     * @param string $parentUuid
     * @return Collection
     */
    public function findAllByFileUuid(string $parentUuid): Collection;

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @param string $excludeUuid
     * @param string|null $oldFileUuid
     * @return Collection
     */
    public function findAllByFileableExceptUuid(string $fileableType, string $fileableId, string $excludeUuid, ?string $oldFileUuid): Collection;

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @return string|null
     */
    public function getRootUuid(string $fileableType, string $fileableId): ?string;

    /**
     * @param array $params
     * @return Collection
     */
    public function findAllWithParams(array $params): Collection;
}
