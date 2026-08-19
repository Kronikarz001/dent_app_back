<?php

namespace App\Repositories;

use App\Models\Dentist;
use App\Search\DentistSearch;
use App\Search\Search;

/**
 * Summary of DentistRepository
 */
class DentistRepository extends SearchableRepository implements DentistRepositoryInterface
{
    /**
     * @var string
     */
    protected string $modelClass = Dentist::class;

    /**
     * @param DentistSearch $search
     */
    public function __construct(
        private readonly DentistSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
