<?php

namespace App\Repositories;

use App\Models\Medicine;
use App\Search\MedicineSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of MedicineRepository
 */
class MedicineRepository extends SearchableRepository implements MedicineRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Medicine::class;

    /**
     * @param MedicineSearch $search
     */
    public function __construct(
        private MedicineSearch $search
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
     * @return Medicine|null
     */
    public function findByUuid(string $uuid): ?Medicine
    {
        return Medicine::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Medicine
     */
    public function create(array $data): Medicine
    {
        return Medicine::create($data);
    }

    /**
     * @param Medicine|Model $model
     * @param array $data
     * @return Medicine
     */
    public function update(Medicine|Model $model, array $data): Medicine
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Medicine|Model $model
     * @return bool
     */
    public function delete(Medicine|Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param string $eanGtin
     * @param array $data
     * @return Medicine
     */
    public function updateOrCreateByEanGtin(string $eanGtin, array $data): Medicine
    {
        return Medicine::query()->updateOrCreate(['ean_gtin' => $eanGtin], $data);
    }
}
