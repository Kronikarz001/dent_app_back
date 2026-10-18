<?php

namespace Tests\Unit\Repositories;

use App\Models\JobPosition;
use App\Repositories\JobPositionRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of JobPositionRepositoryTest
 */
class JobPositionRepositoryTest extends TestCase
{
    /**
     * @var JobPositionRepositoryInterface
     */
    private JobPositionRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(JobPositionRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsJobPosition(): void
    {
        $jobPosition = $this->repository->create([
            'name' => 'Testowe stanowisko',
            'f_name' => 'Testowego stanowiska',
            'm_name' => 'Testowemu stanowisku',
        ]);

        $this->assertDatabaseHas('job_positions', ['uuid' => $jobPosition->uuid, 'name' => 'Testowe stanowisko']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingJobPosition(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $this->assertTrue($this->repository->findByUuid($jobPosition->uuid)->is($jobPosition));
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsNullWhenMissing(): void
    {
        $this->assertNull($this->repository->findByUuid((string) Str::uuid()));
    }

    /**
     * @return void
     */
    public function testUpdateChangesJobPositionAttributes(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $updated = $this->repository->update($jobPosition, ['name' => 'Zmienione']);

        $this->assertSame('Zmienione', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteSoftDeletesJobPosition(): void
    {
        $jobPosition = JobPosition::factory()->create();

        $this->assertTrue($this->repository->delete($jobPosition));
        $this->assertSoftDeleted($jobPosition);
    }
}
