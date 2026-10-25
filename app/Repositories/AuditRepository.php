<?php

namespace App\Repositories;

use App\Exceptions\InvalidAuditableIdentifierException;
use App\Models\Audit;
use App\Search\AuditSearch;
use App\Search\Search;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

/**
 * Summary of AuditRepository
 *
 * @extends SearchableRepository<Audit>
 */
class AuditRepository extends SearchableRepository implements AuditRepositoryInterface
{
    /**
     * @var class-string<Audit>
     */
    protected string $modelClass = Audit::class;

    /**
     * @param AuditSearch $search
     */
    public function __construct(
        private readonly AuditSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $modelClass
     * @param string $uuid
     * @return Model
     *
     * @throws InvalidAuditableIdentifierException
     */
    public function findAuditableOrFail(string $modelClass, string $uuid): Model
    {
        try {
            return $modelClass::query()->findOrFail($uuid);
        } catch (QueryException|ModelNotFoundException $e) {
            throw new InvalidAuditableIdentifierException("Invalid identifier [{$uuid}] for [{$modelClass}].");
        }
    }

    /**
     * @param Model $model
     * @param string $attribute
     * @param string $value
     * @return Audit|null
     */
    public function findLatestChangeTo(Model $model, string $attribute, string $value): ?Audit
    {
        return $this->query()
            ->where('auditable_type', $model->getMorphClass())
            ->where('auditable_id', $model->getKey())
            ->where("change_to->{$attribute}", $value)
            ->latest()
            ->first();
    }
}
