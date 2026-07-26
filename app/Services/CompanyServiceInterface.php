<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of CompanyServiceInterface
 */
interface CompanyServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getCompanies(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data): Company;

    /**
     * @param Company $company
     * @param array $data
     * @return Company
     */
    public function updateCompany(Company $company, array $data): Company;

    /**
     * @param Company $company
     * @return void
     */
    public function deleteCompany(Company $company): void;
}
