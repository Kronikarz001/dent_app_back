<?php

namespace App\Repositories;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection;

/**
 * Summary of FileRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<File>
 */
interface FileRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param string $parentParentUuid
     * @return Collection<int, File>
     */
    public function findAllByFileUuid(string $parentParentUuid): Collection;

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @param string $excludeUuid
     * @param string|null $oldFileUuid
     * @return Collection<int, File>
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
     * @return Collection<int, File>
     */
    public function findAllWithParams(array $params): Collection;
}
