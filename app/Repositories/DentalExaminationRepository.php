<?php

namespace App\Repositories;

use App\Models\DentalExamination;
use App\Search\DentalExaminationSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of DentalExaminationRepository
 */
class DentalExaminationRepository extends SearchableRepository implements DentalExaminationRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = DentalExamination::class;

    /**
     * @param DentalExaminationSearch $search
     */
    public function __construct(
        private DentalExaminationSearch $search
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
     * @return DentalExamination|null
     */
    public function findByUuid(string $uuid): ?DentalExamination
    {
        return DentalExamination::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return DentalExamination
     */
    public function create(array $data): DentalExamination
    {
        return DentalExamination::create($data);
    }

    /**
     * @param DentalExamination|Model $model
     * @param array $data
     * @return DentalExamination
     */
    public function update(DentalExamination|Model $model, array $data): DentalExamination
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param DentalExamination|Model $model
     * @return bool
     */
    public function delete(DentalExamination|Model $model): bool
    {
        return $model->delete();
    }
}
