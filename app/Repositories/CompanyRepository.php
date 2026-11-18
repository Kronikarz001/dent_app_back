<?php

namespace App\Repositories;

use App\Models\Company;
use App\Search\CompanySearch;
use App\Search\Search;

/**
 * Summary of CompanyRepository
 *
 * @extends SearchableRepository<Company>
 */
class CompanyRepository extends SearchableRepository implements CompanyRepositoryInterface
{
    /**
     * @var class-string<Company>
     */
    protected string $modelClass = Company::class;

    /**
     * @param CompanySearch $search
     */
    public function __construct(
        private readonly CompanySearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
