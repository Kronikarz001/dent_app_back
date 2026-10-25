<?php

namespace App\Repositories;

use App\Models\Dentist;
use App\Search\DentistSearch;
use App\Search\Search;

/**
 * Summary of DentistRepository
 *
 * @extends SearchableRepository<Dentist>
 */
class DentistRepository extends SearchableRepository implements DentistRepositoryInterface
{
    /**
     * @var class-string<Dentist>
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
