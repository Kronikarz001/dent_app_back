<?php

namespace Tests\Unit\Repositories;

use App\Models\Company;
use App\Repositories\CompanyRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of CompanyRepositoryTest
 */
class CompanyRepositoryTest extends TestCase
{
    /**
     * @var CompanyRepositoryInterface
     */
    private CompanyRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CompanyRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testExistsByRegonReturnsTrueForMatchingCompany(): void
    {
        Company::factory()->create(['regon' => '123456789']);

        $this->assertTrue($this->repository->existsByRegon('123456789'));
        $this->assertFalse($this->repository->existsByRegon('000000000'));
    }

    /**
     * @return void
     */
    public function testExistsByRegonIgnoresGivenUuid(): void
    {
        $company = Company::factory()->create(['regon' => '123456789']);

        $this->assertFalse($this->repository->existsByRegon('123456789', $company->uuid));
    }

    /**
     * @return void
     */
    public function testExistsByNipReturnsTrueForMatchingCompany(): void
    {
        Company::factory()->create(['nip' => '9876543210']);

        $this->assertTrue($this->repository->existsByNip('9876543210'));
        $this->assertFalse($this->repository->existsByNip('0000000000'));
    }

    /**
     * @return void
     */
    public function testExistsByNipIgnoresGivenUuid(): void
    {
        $company = Company::factory()->create(['nip' => '9876543210']);

        $this->assertFalse($this->repository->existsByNip('9876543210', $company->uuid));
    }

    /**
     * @return void
     */
    public function testDeleteSoftDeletesCompany(): void
    {
        $company = Company::factory()->create();

        $this->assertTrue($this->repository->delete($company));
        $this->assertSoftDeleted($company);
    }
}
