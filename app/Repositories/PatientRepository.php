<?php

namespace App\Repositories;

use App\Models\Patient;
use App\Search\PatientSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

class PatientRepository extends SearchableRepository implements PatientRepositoryInterface
{
    protected string $modelClass = Patient::class;

    public function __construct(
        private readonly PatientSearch $search
    ) {}

    protected function getSearchModel(): Search
    {
        return $this->search;
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