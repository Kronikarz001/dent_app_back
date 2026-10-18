<?php

namespace Tests\Unit\Models;

use App\Enums\PrescriptionStatus;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PrescriptionTest
 */
final class PrescriptionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testStatusIsCastToEnum(): void
    {
        $prescription = Prescription::factory()->create(['status' => PrescriptionStatus::ISSUED]);

        $this->assertSame(PrescriptionStatus::ISSUED, $prescription->status);
    }

    /**
     * @return void
     */
    public function testPatientReturnsOwningPatient(): void
    {
        $patient = Patient::factory()->create();
        $prescription = Prescription::factory()->create(['patient_uuid' => $patient->uuid]);

        $this->assertTrue($prescription->patient->is($patient));
    }

    /**
     * @return void
     */
    public function testDoctorReturnsAssignedUser(): void
    {
        $doctor = User::factory()->create();
        $prescription = Prescription::factory()->create(['doctor_uuid' => $doctor->uuid]);

        $this->assertTrue($prescription->doctor->is($doctor));
    }

    /**
     * @return void
     */
    public function testMedicinesReturnsAttachedMedicinesWithPivotData(): void
    {
        $prescription = Prescription::factory()->create();
        $medicine = Medicine::factory()->create();

        $prescription->medicines()->attach($medicine->uuid, ['quantity' => 3, 'dosage' => '1 co 8h']);

        $attached = $prescription->medicines->first();
        $this->assertTrue($attached->is($medicine));
        $this->assertSame(3, $attached->pivot->quantity);
        $this->assertSame('1 co 8h', $attached->pivot->dosage);
    }
}
