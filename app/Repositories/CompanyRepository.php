<?php

namespace App\Repositories;

use App\Models\Company;
use App\Search\CompanySearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of CompanyRepository
 */
class CompanyRepository extends SearchableRepository implements CompanyRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Company::class;

    /**
     * @param CompanySearch $search
     */
    public function __construct(
        private CompanySearch $search
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
     * @return Company|null
     */
    public function findByUuid(string $uuid): ?Company
    {
        return Company::where('uuid', $uuid)->first();
    }

    /**
     * @param array $data
     * @return Company
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * @param Company|Model $model
     * @param array $data
     * @return Company
     */
    public function update(Company|Model $model, array $data): Company
    {
        $model->update($data);

        return $model->fresh();
    }

    /**
     * @param Company|Model $model
     * @return bool
     */
    public function delete(Company|Model $model): bool
    {
        return $model->delete();
    }

    /**
     * @param string $regon
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsByRegon(string $regon, ?string $ignoreUuid = null): bool
    {
        return Company::query()
            ->where('regon', $regon)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }

    /**
     * @param string $nip
     * @param string|null $ignoreUuid
     * @return bool
     */
    public function existsByNip(string $nip, ?string $ignoreUuid = null): bool
    {
        return Company::query()
            ->where('nip', $nip)
            ->when($ignoreUuid, fn ($query) => $query->where('uuid', '!=', $ignoreUuid))
            ->exists();
    }
}
