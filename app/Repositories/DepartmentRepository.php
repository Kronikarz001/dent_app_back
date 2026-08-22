<?php

namespace App\Repositories;

use App\Models\Department;
use App\Search\DepartmentSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of DepartmentRepository
 */
class DepartmentRepository extends SearchableRepository implements DepartmentRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Department::class;

    /**
     * @param DepartmentSearch $search
     */
    public function __construct(
        private readonly DepartmentSearch $search
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
     * @return Department|null
     */
    public function findByUuid(string $uuid): ?Department
    {
        return Department::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Department
     */
    public function create(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * @param Department|Model $model
     * @param array $data
     * @return Department
     */
    public function update(Department|Model $model, array $data): Department
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Department|Model $model
     * @return bool
     */
    public function delete(Department|Model $model): bool
    {
        return $model->delete();
    }
}
