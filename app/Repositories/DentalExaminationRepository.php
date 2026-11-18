<?php

namespace App\Repositories;

use App\Models\DentalExamination;
use App\Search\DentalExaminationSearch;
use App\Search\Search;

/**
 * Summary of DentalExaminationRepository
 *
 * @extends SearchableRepository<DentalExamination>
 */
class DentalExaminationRepository extends SearchableRepository implements DentalExaminationRepositoryInterface
{
    /**
     * @var class-string<DentalExamination>
     */
    protected string $modelClass = DentalExamination::class;

    /**
     * @param DentalExaminationSearch $search
     */
    public function __construct(
        private readonly DentalExaminationSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
