<?php

namespace Tests\Unit\Repositories;

use App\Models\DentalExamination;
use App\Repositories\DentalExaminationRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of DentalExaminationRepositoryTest
 */
class DentalExaminationRepositoryTest extends TestCase
{
    /**
     * @var DentalExaminationRepositoryInterface
     */
    private DentalExaminationRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(DentalExaminationRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsDentalExamination(): void
    {
        $examination = $this->repository->create(['name' => 'Test badanie']);

        $this->assertDatabaseHas('dental_examinations', ['uuid' => $examination->uuid, 'name' => 'Test badanie']);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingExamination(): void
    {
        $examination = DentalExamination::factory()->create();

        $this->assertTrue($this->repository->findByUuid($examination->uuid)->is($examination));
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
    public function testUpdateChangesExaminationAttributes(): void
    {
        $examination = DentalExamination::factory()->create();

        $updated = $this->repository->update($examination, ['name' => 'Zmienione']);

        $this->assertSame('Zmienione', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesExamination(): void
    {
        $examination = DentalExamination::factory()->create();

        $this->assertTrue($this->repository->delete($examination));
        $this->assertDatabaseMissing('dental_examinations', ['uuid' => $examination->uuid]);
    }
}
