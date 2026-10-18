<?php

namespace Tests\Unit\Repositories;

use App\Models\Patient;
use App\Repositories\PatientRepositoryInterface;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Summary of PatientRepositoryTest
 */
class PatientRepositoryTest extends TestCase
{
    /**
     * @var PatientRepositoryInterface
     */
    private PatientRepositoryInterface $repository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PatientRepositoryInterface::class);
    }

    /**
     * @return void
     */
    public function testCreatePersistsPatient(): void
    {
        $patient = $this->repository->create(Patient::factory()->make()->toArray());

        $this->assertDatabaseHas('patients', ['uuid' => $patient->uuid]);
    }

    /**
     * @return void
     */
    public function testFindByUuidReturnsMatchingPatient(): void
    {
        $patient = Patient::factory()->create();

        $this->assertTrue($this->repository->findByUuid($patient->uuid)->is($patient));
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
    public function testFindAllWithRomexisIdOnlyReturnsPatientsWithRomexisIdSet(): void
    {
        $withId = Patient::factory()->create(['romexis_patient_id' => '12345']);
        Patient::factory()->create(['romexis_patient_id' => null]);

        $result = $this->repository->findAllWithRomexisId();

        $this->assertCount(1, $result->items());
        $this->assertSame($withId->uuid, $result->items()[0]->uuid);
    }

    /**
     * @return void
     */
    public function testUpdateChangesPatientAttributes(): void
    {
        $patient = Patient::factory()->create();

        $updated = $this->repository->update($patient, ['first_name' => 'Zmienione']);

        $this->assertSame('Zmienione', $updated->first_name);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesPatient(): void
    {
        $patient = Patient::factory()->create();

        $this->assertTrue($this->repository->delete($patient));
        $this->assertDatabaseMissing('patients', ['uuid' => $patient->uuid]);
    }
}
