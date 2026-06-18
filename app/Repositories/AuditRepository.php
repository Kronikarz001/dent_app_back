<?php

namespace App\Repositories;

use App\Models\Audit;
use App\Search\AuditSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of AuditRepository
 */
class AuditRepository extends SearchableRepository implements AuditRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Audit::class;

    /**
     * @param AuditSearch $search
     */
    public function __construct(
        private AuditSearch $search
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
     * @param array $data
     * @return Audit
     */
    public function create(array $data): Audit
    {
        return Audit::create($data);
    }

    /**
     * @param string $modelClass
     * @param string $uuid
     * @return Model
     */
    public function findAuditableOrFail(string $modelClass, string $uuid): Model
    {
        return $modelClass::query()->findOrFail($uuid);
    }
}
