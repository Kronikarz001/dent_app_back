<?php

namespace App\Repositories;

use App\Models\File;
use App\Search\FileSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of FileRepository
 */
class FileRepository extends SearchableRepository implements FileRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = File::class;

    /**
     * @param FileSearch $search
     */
    public function __construct(
        private readonly FileSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $uuid
     * @return File
     */
    public function findByUuid(string $uuid): File
    {
        return File::whereUuid($uuid)->first();
    }

    /**
     * @param array $data
     * @return File
     */
    public function create(array $data): File
    {
        return File::create($data);
    }

    /**
     * @param Model|File $model
     * @param array $data
     * @return File
     */
    public function update(Model|File $model, array $data): File
    {
        $model->update($data);

        return $model;
    }

    /**
     * @param Model|File $model
     * @return bool
     */
    public function delete(Model|File $model): bool
    {
        return $model->delete();
    }

    /**
     * @param string $parentParentUuid
     * @return Collection
     */
    public function findAllByFileUuid(string $parentParentUuid): Collection
    {
        return File::whereFileUuid($parentParentUuid)->get();
    }

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @param string $excludeUuid
     * @param string|null $oldFileUuid
     * @return Collection
     */
    public function findAllByFileableExceptUuid(string $fileableType, string $fileableId, string $excludeUuid, ?string $oldFileUuid): Collection
    {
        return File::query()
            ->where('fileable_type', $fileableType)
            ->where('fileable_id', $fileableId)
            ->where('uuid', '<>', $excludeUuid)
            ->when($oldFileUuid !== null, function ($q) use ($oldFileUuid) {
                $q->where(function ($q2) use ($oldFileUuid) {
                    $q2->where('uuid', $oldFileUuid)
                        ->orWhere('file_uuid', $oldFileUuid);
                });
            })
            ->get();
    }

    /**
     * @param string $fileableType
     * @param string $fileableId
     * @return string|null
     */
    public function getRootUuid(string $fileableType, string $fileableId): ?string
    {
        return File::query()
            ->where('fileable_type', $fileableType)
            ->where('fileable_id', $fileableId)
            ->orderBy('created_at', 'asc')
            ->value('uuid');
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function findAllWithParams(array $params): Collection
    {
        return File::query()->where($params)->get();
    }
}
