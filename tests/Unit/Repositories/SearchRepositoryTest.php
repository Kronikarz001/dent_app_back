<?php

namespace Tests\Unit\Repositories;

use App\Models\Patient;
use App\Repositories\SearchRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of SearchRepositoryTest
 */
class SearchRepositoryTest extends TestCase
{
    /**
     * @var SearchRepositoryInterface
     */
    private SearchRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(SearchRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testPaginateWithPositivePerPageLimitsResults(): void
    {
        Patient::factory()->count(3)->create();
        $query = $this->repository->createQuery(Patient::class);

        $result = $this->repository->paginate($query, 2);

        $this->assertCount(2, $result->items());
    }

    /**
     * @return void
     */
    public function testPaginateWithNegativePerPageReturnsAllResults(): void
    {
        Patient::factory()->count(3)->create();
        $query = $this->repository->createQuery(Patient::class);

        $result = $this->repository->paginate($query, -1);

        $this->assertCount(3, $result->items());
    }

    /**
     * @return void
     */
    public function testWhereInFiltersResultsToGivenValues(): void
    {
        $patient = Patient::factory()->create();
        Patient::factory()->create();
        $query = $this->repository->createQuery(Patient::class);

        $this->repository->whereIn($query, 'uuid', [$patient->uuid]);
        $result = $this->repository->paginate($query, -1);

        $this->assertCount(1, $result->items());
    }
}
