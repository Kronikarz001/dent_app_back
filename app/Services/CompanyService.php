<?php

namespace App\Services;

use App\Models\Company;
use App\Repositories\CompanyRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of CompanyService
 */
readonly class CompanyService implements CompanyServiceInterface
{
    /**
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getCompanies(): LengthAwarePaginator
    {
        return $this->companyRepository->findAllWithPagination();
    }

    /**
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data): Company
    {
        return $this->companyRepository->create($data);
    }

    /**
     * @param Company $company
     * @param array $data
     * @return Company
     */
    public function updateCompany(Company $company, array $data): Company
    {
        return $this->companyRepository->update($company, $data);
    }

    /**
     * @param Company $company
     * @return void
     */
    public function deleteCompany(Company $company): void
    {
        $this->companyRepository->delete($company);
    }
}
