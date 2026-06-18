<?php

namespace App\Repositories;

use App\Models\Audit;

/**
 * Summary of AuditRepositoryInterface
 */
interface AuditRepositoryInterface extends BasicRepositoryInterface
{
    /**
     * @param array $data
     * @return Audit
     */
    public function create(array $data): Audit;
}
