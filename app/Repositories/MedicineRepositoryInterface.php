<?php

namespace App\Repositories;

use App\Models\Medicine;

/**
 * Summary of MedicineRepositoryInterface
 *
 * @extends SearchableRepositoryInterface<Medicine>
 */
interface MedicineRepositoryInterface extends SearchableRepositoryInterface
{
    /**
     * @param string $eanGtin
     * @param array $data
     * @return Medicine
     */
    public function updateOrCreateByEanGtin(string $eanGtin, array $data): Medicine;
}
