<?php

namespace Tests\Unit\Repositories;

use App\Models\Medicine;
use App\Repositories\MedicineRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of MedicineRepositoryTest
 */
class MedicineRepositoryTest extends TestCase
{
    /**
     * @var MedicineRepositoryInterface
     */
    private MedicineRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MedicineRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsMedicine(): void
    {
        $medicine = $this->repository->create(Medicine::factory()->make()->toArray());

        $this->assertDatabaseHas('medicines', ['uuid' => $medicine->uuid]);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingMedicine(): void
    {
        $medicine = Medicine::factory()->create();

        $this->assertTrue($this->repository->findByUuid($medicine->uuid)->is($medicine));
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
    public function testUpdateChangesMedicineAttributes(): void
    {
        $medicine = Medicine::factory()->create();

        $updated = $this->repository->update($medicine, ['name' => 'Zmieniony']);

        $this->assertSame('Zmieniony', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesMedicine(): void
    {
        $medicine = Medicine::factory()->create();

        $this->assertTrue($this->repository->delete($medicine));
        $this->assertDatabaseMissing('medicines', ['uuid' => $medicine->uuid]);
    }
}
