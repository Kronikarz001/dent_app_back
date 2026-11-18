<?php

namespace App\Repositories;

use App\Models\Material;
use App\Search\MaterialSearch;
use App\Search\Search;

/**
 * Summary of MaterialRepository
 *
 * @extends SearchableRepository<Material>
 */
class MaterialRepository extends SearchableRepository implements MaterialRepositoryInterface
{
    /**
     * @var class-string<Material>
     */
    protected string $modelClass = Material::class;

    /**
     * @param MaterialSearch $search
     */
    public function __construct(
        private readonly MaterialSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
