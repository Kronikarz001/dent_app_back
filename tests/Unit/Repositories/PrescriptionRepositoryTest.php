<?php

namespace Tests\Unit\Repositories;

use App\Models\Patient;
use App\Models\Prescription;
use App\Repositories\PrescriptionRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of PrescriptionRepositoryTest
 */
class PrescriptionRepositoryTest extends TestCase
{
    /**
     * @var PrescriptionRepositoryInterface
     */
    private PrescriptionRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PrescriptionRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsPrescription(): void
    {
        $prescription = $this->repository->create(Prescription::factory()->make()->toArray());

        $this->assertDatabaseHas('prescriptions', ['uuid' => $prescription->uuid]);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingPrescription(): void
    {
        $prescription = Prescription::factory()->create();

        $this->assertTrue($this->repository->findByUuid($prescription->uuid)->is($prescription));
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
    public function testFindAllByPatientOnlyReturnsThatPatientsPrescriptions(): void
    {
        $patient = Patient::factory()->create();
        $own = Prescription::factory()->create(['patient_uuid' => $patient->uuid]);
        Prescription::factory()->create();

        $result = $this->repository->findAllByPatient($patient->uuid);

        $this->assertTrue($result->getCollection()->contains(fn (Prescription $item) => $item->uuid === $own->uuid));
        $this->assertCount(1, $result->items());
    }

    /**
     * @return void
     */
    public function testUpdateChangesPrescriptionAttributes(): void
    {
        $prescription = Prescription::factory()->create();

        $updated = $this->repository->update($prescription, ['name' => 'Zmieniona']);

        $this->assertSame('Zmieniona', $updated->name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesPrescription(): void
    {
        $prescription = Prescription::factory()->create();

        $this->assertTrue($this->repository->delete($prescription));
        $this->assertDatabaseMissing('prescriptions', ['uuid' => $prescription->uuid]);
    }
}
