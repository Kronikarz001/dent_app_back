<?php

namespace App\Repositories;

use App\Models\EmployeeSchedule;
use App\Search\EmployeeScheduleSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of EmployeeScheduleRepository
 */
readonly class EmployeeScheduleRepository implements EmployeeScheduleRepositoryInterface
{
    /**
     * @param EmployeeScheduleSearch $search
     */
    public function __construct(
        private EmployeeScheduleSearch $search
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
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    /**
     * @param string $uuid
     * @return EmployeeSchedule|null
     */
    public function findByUuid(string $uuid): ?EmployeeSchedule
    {
        return EmployeeSchedule::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return EmployeeSchedule
     */
    public function create(array $data): EmployeeSchedule
    {
        return EmployeeSchedule::create($data);
    }

    /**
     * @param EmployeeSchedule|Model $model
     * @param array $data
     * @return EmployeeSchedule
     */
    public function update(EmployeeSchedule|Model $model, array $data): EmployeeSchedule
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param array $uuids
     * @return Collection
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return EmployeeSchedule::whereIn('uuid', $uuids)->get();
    }

    /**
     * @param string $modelClass
     * @param array $uniqueAttributes
     * @param array $values
     * @return EmployeeSchedule
     */
    public function createOrUpdate(string $modelClass, array $uniqueAttributes, array $values): EmployeeSchedule
    {
        return $modelClass::updateOrCreate($uniqueAttributes, $values);
    }

    /**
     * @param EmployeeSchedule|Model $model
     * @return bool
     */
    public function delete(EmployeeSchedule|Model $model): bool
    {
        return $model->delete();
    }
}
