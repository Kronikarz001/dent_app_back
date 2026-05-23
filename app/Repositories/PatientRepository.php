<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Search\PatientSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PatientRepository
 */
readonly class PatientRepository implements PatientRepositoryInterface
{
    public function __construct(
        private PatientSearch $search
    ) {}

    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    public function findSelectAllWithPagination(array $columns = ['*'], array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    public function findByUuid(string $uuid): ?Patient
    {
        return Patient::where('uuid', $uuid)->first();
    }

    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    public function update(Patient|Model $model, array $data): Patient
    {
        $model->update($data);

        return $model->fresh();
    }

    public function delete(Patient|Model $model): bool
    {
        return $model->delete();
    }
}
