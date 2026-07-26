<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Repositories\CompanyRepositoryInterface;
use App\Services\CompanyService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Summary of CompanyServiceTest
 */
class CompanyServiceTest extends TestCase
{
    private MockInterface $companyRepository;

    private CompanyService $companyService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->companyRepository = Mockery::mock(CompanyRepositoryInterface::class);
        $this->companyService = new CompanyService($this->companyRepository);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testGetCompaniesDelegatesToRepositoryWithoutColumnFilter(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->companyRepository
            ->shouldReceive('findAllWithPagination')
            ->once()
            ->withNoArgs()
            ->andReturn($paginator);

        $result = $this->companyService->getCompanies();

        $this->assertSame($paginator, $result);
    }

    /**
     * @return void
     */
    public function testCreateCompanyPassesDataUnchangedToRepository(): void
    {
        $data = ['name' => 'Dentica sp. z o.o.'];
        $newCompany = Company::factory()->make();

        $this->companyRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($newCompany);

        $result = $this->companyService->createCompany($data);

        $this->assertSame($newCompany, $result);
    }

    /**
     * @return void
     */
    public function testUpdateCompanyPassesCompanyAndDataToRepository(): void
    {
        $company = Company::factory()->make();
        $data = ['name' => 'Updated'];
        $updatedCompany = Company::factory()->make(['name' => 'Updated']);

        $this->companyRepository
            ->shouldReceive('update')
            ->once()
            ->with($company, $data)
            ->andReturn($updatedCompany);

        $result = $this->companyService->updateCompany($company, $data);

        $this->assertSame($updatedCompany, $result);
    }

    /**
     * @return void
     */
    public function testDeleteCompanyDelegatesToRepository(): void
    {
        $this->expectNotToPerformAssertions();
        $company = Company::factory()->make();

        $this->companyRepository
            ->shouldReceive('delete')
            ->once()
            ->with($company);

        $this->companyService->deleteCompany($company);
    }
}
