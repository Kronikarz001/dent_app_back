<?php

namespace Tests\Feature\Controller;

use App\Models\Patient;
use Tests\TestCase;

/**
 * Summary of PatientControllerTest
 */
class PatientControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexPatientsReturnSuccessResponse(): void
    {
        Patient::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testIndexPatientsListReturnSuccessResponse(): void
    {
        Patient::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.selectList'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testShowPatientReturnSuccessResponse(): void
    {
        $patient = Patient::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.show', ['patient' => $patient->uuid]));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testStorePatientReturnCreatedResponse(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->postJson(route('patient.store'), [
                'first_name' => 'Jan',
                'last_name' => 'Kowalski',
                'email' => 'jan@example.com',
                'pesel' => '12345678901',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('patients', [
            'email' => 'jan@example.com',
        ]);
    }

    /**
     * @return void
     */
    public function testUpdatePatientReturnNoContentResponse(): void
    {
        $patient = Patient::factory()->create();
        $response = $this->callApiWithLoggedUser()
            ->putJson(route('patient.update', ['patient' => $patient->uuid]), [
                'first_name' => 'Updated',
                'last_name' => 'Kowalski',
                'email' => 'updated@example.com',
            ]);

        $response->assertNoContent();

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Updated',
            'last_name' => 'Kowalski',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * @return void
     */
    public function testDeletePatientReturnNoContentResponse(): void
    {
        $patient = Patient::factory()->create();
        $this->callApiWithLoggedUser()
            ->deleteJson(route('patient.destroy', ['patient' => $patient->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('patients', ['uuid' => $patient->uuid]);
    }

    /**
     * @return void
     */
    public function testExportPatientReturnSuccessResponse(): void
    {
        Patient::factory()->count(5)->create();
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.export', ['type' => 'xlsx']));

        $response->assertOk();
    }
}
