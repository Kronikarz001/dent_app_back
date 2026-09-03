<?php

namespace Tests\Feature\Services;

use App\Enums\PhoneNumberType;
use App\Models\Patient;
use App\Services\PatientServiceInterface;
use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PatientServiceTest extends TestCase
{
    /**
     * @var PatientServiceInterface|Application|mixed|object
     */
    private PatientServiceInterface $service;

    protected const PATIENTS_TABLE = 'patients';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PatientServiceInterface::class);
    }

    /**
     * @return void
     */
    public function testGetPatientsReturnsPaginatedResults(): void
    {
        Patient::factory()->count(3)->create();

        $result = $this->service->getPatients();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @return void
     */
    public function testGetPatientsListReturnsPaginator(): void
    {
        Patient::factory()->count(2)->create();

        $result = $this->service->getPatientsList();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreatePatientPersistsToDatabase(): void
    {
        $data = Patient::factory()->make()->toArray();

        $patient = $this->service->createPatient($data);

        $this->assertInstanceOf(Patient::class, $patient);
        $this->assertDatabaseHas(self::PATIENTS_TABLE, ['email' => $data['email']]);
    }

    /**
     * @return void
     */
    public function testUpdatePatientPersistsChangesToDatabase(): void
    {
        $patient = Patient::factory()->create();

        $result = $this->service->updatePatient($patient, ['first_name' => 'Zmienione']);

        $this->assertInstanceOf(Patient::class, $result);
        $this->assertDatabaseHas(self::PATIENTS_TABLE, ['uuid' => $patient->uuid, 'first_name' => 'Zmienione']);
    }

    /**
     * @return void
     */
    public function testUpdatePatientAssignsPhoneNumbers(): void
    {
        $patient = Patient::factory()->create();

        $this->service->updatePatient($patient, [
            'phone_numbers' => [
                ['number' => '48500100200', 'type' => PhoneNumberType::PRIVATE->value],
            ],
        ]);

        $this->assertDatabaseHas('phone_numbers', [
            'phoneable_type' => Patient::class,
            'phoneable_uuid' => $patient->uuid,
            'number' => '48500100200',
            'type' => PhoneNumberType::PRIVATE->value,
        ]);
    }

    /**
     * @return void
     */
    public function testDeletePatientRemovesFromDatabase(): void
    {
        $patient = Patient::factory()->create();

        $this->service->deletePatient($patient);

        $this->assertDatabaseMissing(self::PATIENTS_TABLE, ['uuid' => $patient->uuid]);
    }
}
