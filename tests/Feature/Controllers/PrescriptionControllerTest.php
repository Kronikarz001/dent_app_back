<?php

namespace Tests\Feature\Controllers;

use App\Enums\PrescriptionStatus;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Summary of PrescriptionControllerTest
 */
class PrescriptionControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testIndexPrescriptionsReturnSuccessResponse(): void
    {
        Prescription::factory()->count(3)->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('prescription.index'));

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function testByPatientReturnsOnlyThatPatientsPrescriptions(): void
    {
        $patient = Patient::factory()->create();
        $ownPrescription = Prescription::factory()->create(['patient_uuid' => $patient->uuid]);
        Prescription::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('patient.prescriptions', ['patient' => $patient->uuid]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $ownPrescription->uuid);
    }

    /**
     * @return void
     */
    public function testByPatientQueryCountDoesNotGrowWithNumberOfPrescriptions(): void
    {
        $patient = Patient::factory()->create(['doctor_uuid' => User::factory()]);
        Prescription::factory()->count(2)->create(['patient_uuid' => $patient->uuid, 'doctor_uuid' => User::factory()]);

        $api = $this->callApiWithLoggedUser();
        $countQueries = function () use ($api, $patient): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $api->getJson(route('patient.prescriptions', ['patient' => $patient->uuid]))->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        $countQueries();
        $withTwo = $countQueries();
        Prescription::factory()->count(4)->create(['patient_uuid' => $patient->uuid, 'doctor_uuid' => User::factory()]);
        $withSix = $countQueries();

        $this->assertSame($withTwo, $withSix);
    }

    /**
     * @return void
     */
    public function testShowPrescriptionReturnsNestedPatientDoctorAndMedicines(): void
    {
        $prescription = Prescription::factory()->create();
        $medicine = Medicine::factory()->create();
        $prescription->medicines()->attach($medicine->uuid, ['quantity' => 2, 'dosage' => '1 tabletka rano']);

        $response = $this->callApiWithLoggedUser()
            ->getJson(route('prescription.show', ['prescription' => $prescription->uuid]));

        $response->assertOk();
        $response->assertJsonPath('uuid', $prescription->uuid);
        $response->assertJsonPath('patient.uuid', $prescription->patient_uuid);
        $response->assertJsonPath('doctor.uuid', $prescription->doctor_uuid);
        $response->assertJsonPath('medicines.0.uuid', $medicine->uuid);
        $response->assertJsonPath('medicines.0.quantity', 2);
        $response->assertJsonPath('medicines.0.dosage', '1 tabletka rano');
    }

    /**
     * @return void
     */
    public function testShowMissingPrescriptionReturnsNotFound(): void
    {
        $response = $this->callApiWithLoggedUser()
            ->getJson(route('prescription.show', ['prescription' => '019e99cf-9ffe-70a8-9b4c-8b889d28eeff']));

        $response->assertNotFound();
    }

    /**
     * @return void
     */
    public function testStorePrescriptionWithMedicinesPersistsPivotData(): void
    {
        $patient = Patient::factory()->create();
        $doctor = User::factory()->create();
        $medicine = Medicine::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('prescription.store'), [
                'status' => PrescriptionStatus::DRAFT->value,
                'patient_uuid' => $patient->uuid,
                'doctor_uuid' => $doctor->uuid,
                'medicines' => [
                    ['medicine_uuid' => $medicine->uuid, 'quantity' => 1, 'dosage' => '1 tabletka dziennie'],
                ],
            ]);

        $response->assertCreated();

        $prescription = Prescription::query()->where('patient_uuid', $patient->uuid)->first();
        $this->assertDatabaseHas('prescription_medicine', [
            'prescription_uuid' => $prescription->uuid,
            'medicine_uuid' => $medicine->uuid,
            'quantity' => 1,
            'dosage' => '1 tabletka dziennie',
        ]);
    }

    /**
     * @return void
     */
    public function testStoreWithoutPatientReturnsValidationError(): void
    {
        $doctor = User::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('prescription.store'), [
                'status' => PrescriptionStatus::DRAFT->value,
                'doctor_uuid' => $doctor->uuid,
            ]);

        $response->assertStatus(422);
    }

    /**
     * @return void
     */
    public function testUpdateWithMedicinesReplacesPreviousAssignments(): void
    {
        $prescription = Prescription::factory()->create();
        $oldMedicine = Medicine::factory()->create();
        $newMedicine = Medicine::factory()->create();
        $prescription->medicines()->sync([$oldMedicine->uuid => ['quantity' => 1, 'dosage' => 'stara dawka']]);

        $response = $this->callApiWithLoggedUser()
            ->putJson(route('prescription.update', ['prescription' => $prescription->uuid]), [
                'status' => $prescription->status->value,
                'patient_uuid' => $prescription->patient_uuid,
                'doctor_uuid' => $prescription->doctor_uuid,
                'medicines' => [
                    ['medicine_uuid' => $newMedicine->uuid, 'quantity' => 3, 'dosage' => 'nowa dawka'],
                ],
            ]);

        $response->assertNoContent();

        $this->assertDatabaseMissing('prescription_medicine', [
            'prescription_uuid' => $prescription->uuid,
            'medicine_uuid' => $oldMedicine->uuid,
        ]);
        $this->assertDatabaseHas('prescription_medicine', [
            'prescription_uuid' => $prescription->uuid,
            'medicine_uuid' => $newMedicine->uuid,
            'quantity' => 3,
            'dosage' => 'nowa dawka',
        ]);
    }

    /**
     * @return void
     */
    public function testIssuePrescriptionReturnsNotImplementedUntilP1IsConnected(): void
    {
        $prescription = Prescription::factory()->create();

        $response = $this->callApiWithLoggedUser()
            ->postJson(route('prescription.issue', ['prescription' => $prescription->uuid]));

        $response->assertStatus(501);
    }

    /**
     * @return void
     */
    public function testDeletePrescriptionReturnNoContentResponse(): void
    {
        $prescription = Prescription::factory()->create();

        $this->callApiWithLoggedUser()
            ->deleteJson(route('prescription.destroy', ['prescription' => $prescription->uuid]))
            ->assertNoContent();

        $this->assertDatabaseMissing('prescriptions', ['uuid' => $prescription->uuid]);
    }
}
