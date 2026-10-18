<?php

namespace Tests\Unit\Repositories;

use App\Models\Dentist;
use App\Models\JobPosition;
use App\Models\User;
use App\Repositories\DentistRepositoryInterface;
use Tests\TestCase;

/**
 * Summary of DentistRepositoryTest
 */
class DentistRepositoryTest extends TestCase
{
    /**
     * @var DentistRepositoryInterface
     */
    private DentistRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(DentistRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testFindByUuidOnlyReturnsUsersWithDentistJobPosition(): void
    {
        $dentistPosition = JobPosition::factory()->create(['name' => JobPosition::DENTIST_NAME]);
        $otherPosition = JobPosition::factory()->create(['name' => 'Recepcjonista']);

        $dentist = User::factory()->create(['job_position_uuid' => $dentistPosition->uuid]);
        $other = User::factory()->create(['job_position_uuid' => $otherPosition->uuid]);

        $this->assertNotNull($this->repository->findByUuid($dentist->uuid));
        $this->assertNull($this->repository->findByUuid($other->uuid));
    }

    /**
     * @return void
     */
    public function testFindAllWithPaginationOnlyReturnsDentists(): void
    {
        $dentistPosition = JobPosition::factory()->create(['name' => JobPosition::DENTIST_NAME]);
        $otherPosition = JobPosition::factory()->create(['name' => 'Recepcjonista']);

        $dentist = User::factory()->create(['job_position_uuid' => $dentistPosition->uuid]);
        User::factory()->create(['job_position_uuid' => $otherPosition->uuid]);

        $result = $this->repository->findAllWithPagination();

        $this->assertTrue($result->getCollection()->contains(fn (Dentist $item) => $item->uuid === $dentist->uuid));
        $this->assertCount(1, $result->getCollection());
    }
}
