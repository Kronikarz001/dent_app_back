<?php

namespace App\Services;

use App\Exceptions\DuplicateCompanyDataException;
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
        $this->assertUniqueRegonAndNip($data);

        return $this->companyRepository->create($data);
    }

    /**
     * @param Company $company
     * @param array $data
     * @return Company
     */
    public function updateCompany(Company $company, array $data): Company
    {
        $this->assertUniqueRegonAndNip($data, $company);

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

    /**
     * @param array $data
     * @param Company|null $ignore
     * @return void
     */
    private function assertUniqueRegonAndNip(array $data, ?Company $ignore = null): void
    {
        if (array_key_exists('regon', $data) && $this->companyRepository->existsByRegon($data['regon'], $ignore?->uuid)) {
            throw new DuplicateCompanyDataException('Firma o podanym numerze REGON już istnieje.');
        }

        if (array_key_exists('nip', $data) && $this->companyRepository->existsByNip($data['nip'], $ignore?->uuid)) {
            throw new DuplicateCompanyDataException('Firma o podanym numerze NIP już istnieje.');
        }
    }
}
