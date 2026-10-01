<?php

namespace App\Repositories;

use App\Models\Photo;
use App\Search\PhotoSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhotoRepository
 */
class PhotoRepository extends SearchableRepository implements PhotoRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Photo::class;

    /**
     * @param PhotoSearch $search
     */
    public function __construct(
        private readonly PhotoSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param array $data
     * @return Photo
     */
    public function create(array $data): Photo
    {
        return Photo::create($data);
    }

    /**
     * @param Model|Photo $model
     * @param array $data
     * @return Photo
     */
    public function update(Model|Photo $model, array $data): Photo
    {
        $model->update($data);

        return $model;
    }

    /**
     * @param Model|Photo $model
     * @return bool
     */
    public function delete(Model|Photo $model): bool
    {
        return $model->delete();
    }
}
