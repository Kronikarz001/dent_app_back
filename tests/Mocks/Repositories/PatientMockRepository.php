<?php

namespace Tests\Mocks\Repositories;

use App\Models\Patient;
use App\Repositories\PatientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorImpl;

/**
 * Summary of PatientMockRepository
 */
class PatientMockRepository implements PatientRepositoryInterface
{
    /**
     * @param string $uuid
     * @return Patient|null
     */
    public function findByUuid(string $uuid): ?Patient
    {
        return new Patient();
    }

    /**
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient
    {
        return Patient::factory()->make($data);
    }

    /**
     * @param Patient|Model $model
     * @param array $data
     * @return Patient
     */
    public function update(Patient|Model $model, array $data): Patient
    {
        return Patient::factory()->make($data);
    }

    /**
     * @param Model|Patient $model
     * @return bool
     */
    public function delete(Model|Patient $model): bool
    {
        return true;
    }

    /**
     * @param array $uuids
     * @return Collection
     */
    public function findAllByUuids(array $uuids): Collection
    {
        return new Collection();
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    /**
     * @param array $columns
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return new PaginatorImpl([], 0, 10);
    }

    /**
     * @param string $modelClass
     * @param array $uniqueAttributes
     * @param array $values
     * @return Model
     */
    public function createOrUpdate(string $modelClass, array $uniqueAttributes, array $values): Model
    {
        return $modelClass::updateOrCreate($uniqueAttributes, $values);
    }
}
