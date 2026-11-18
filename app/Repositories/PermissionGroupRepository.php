<?php

namespace App\Repositories;

use App\Models\PermissionGroup;
use App\Search\PermissionGroupSearch;
use App\Search\Search;

/**
 * Summary of PermissionGroupRepository
 *
 * @extends SearchableRepository<PermissionGroup>
 */
class PermissionGroupRepository extends SearchableRepository implements PermissionGroupRepositoryInterface
{
    /**
     * @var class-string<PermissionGroup>
     */
    protected string $modelClass = PermissionGroup::class;

    /**
     * @param PermissionGroupSearch $search
     */
    public function __construct(
        private readonly PermissionGroupSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
