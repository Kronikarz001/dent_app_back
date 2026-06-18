<?php

namespace App\Repositories;

use App\Models\Material;
use App\Search\MaterialSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of MaterialRepository
 */
class MaterialRepository extends SearchableRepository implements MaterialRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Material::class;

    /**
     * @param MaterialSearch $search
     */
    public function __construct(
        private MaterialSearch $search
    ) {}

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $uuid
     * @return Material|null
     */
    public function findByUuid(string $uuid): ?Material
    {
        return Material::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Material
     */
    public function create(array $data): Material
    {
        return Material::create($data);
    }

    /**
     * @param Material|Model $model
     * @param array $data
     * @return Material
     */
    public function update(Material|Model $model, array $data): Material
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Material|Model $model
     * @return bool
     */
    public function delete(Material|Model $model): bool
    {
        return $model->delete();
    }
}
