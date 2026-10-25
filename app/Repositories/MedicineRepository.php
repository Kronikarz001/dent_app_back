<?php

namespace App\Repositories;

use App\Models\Medicine;
use App\Search\MedicineSearch;
use App\Search\Search;

/**
 * Summary of MedicineRepository
 *
 * @extends SearchableRepository<Medicine>
 */
class MedicineRepository extends SearchableRepository implements MedicineRepositoryInterface
{
    /**
     * @var class-string<Medicine>
     */
    protected string $modelClass = Medicine::class;

    /**
     * @param MedicineSearch $search
     */
    public function __construct(
        private readonly MedicineSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    /**
     * @param string $eanGtin
     * @param array $data
     * @return Medicine
     */
    public function updateOrCreateByEanGtin(string $eanGtin, array $data): Medicine
    {
        return $this->query()->updateOrCreate(['ean_gtin' => $eanGtin], $data);
    }
}
