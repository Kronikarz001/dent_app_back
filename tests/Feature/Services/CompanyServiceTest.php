<?php

namespace Tests\Feature\Services;

use App\Models\Company;
use App\Services\CompanyServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * Summary of CompanyServiceTest
 */
class CompanyServiceTest extends TestCase
{
    /**
     * @var CompanyServiceInterface|Application|mixed|object
     */
    private CompanyServiceInterface $service;

    protected const COMPANIES_TABLE = 'companies';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CompanyServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetCompaniesReturnsPaginatedResults(): void
    {
        Company::factory()->count(3)->create();

        $result = $this->service->getCompanies();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testCreateCompanyPersistsToDatabase(): void
    {
        $data = Company::factory()->make()->toArray();

        $company = $this->service->createCompany($data);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertDatabaseHas(self::COMPANIES_TABLE, ['uuid' => $company->uuid]);
    }

    /**
     * @return void
     */
    public function testUpdateCompanyPersistsChangesToDatabase(): void
    {
        $company = Company::factory()->create();

        $result = $this->service->updateCompany($company, ['name' => 'Nowa nazwa']);

        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas(self::COMPANIES_TABLE, ['uuid' => $company->uuid, 'name' => 'Nowa nazwa']);
    }

    /**
     * @return void
     */
    public function testDeleteCompanyRemovesFromDatabase(): void
    {
        $company = Company::factory()->create();

        $this->service->deleteCompany($company);

        $this->assertDatabaseMissing(self::COMPANIES_TABLE, ['uuid' => $company->uuid, 'deleted_at' => null]);
    }
}
