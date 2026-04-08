<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Search\PatientSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of PatientRepository
 */
readonly class PatientRepository implements PatientRepositoryInterface
{
    /**
     * @param PatientSearch $search
     */
    public function __construct(
        private PatientSearch $search
    ) {
    }

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
     * @return Patient|null
     */
    public function findByUuid(string $uuid): ?Patient
    {
        return Patient::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    /**
     * @param Patient|Model $model
     * @param array $data
     * @return Patient
     */
    public function update(Patient|Model $model, array $data): Patient
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * @param Patient|Model $model
     * @return bool
     */
    public function delete(Patient|Model $model): bool
    {
        return $model->delete();
    }
}
