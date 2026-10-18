<?php

namespace Tests\Unit\Models;

use App\Enums\ReimbursementLevel;
use App\Models\Medicine;
use App\Models\Prescription;
use Tests\Unit\UnitTestCase;

/**
 * Summary of MedicineTest
 */
final class MedicineTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testReimbursementLevelIsCastToEnum(): void
    {
        $medicine = Medicine::factory()->create(['reimbursement_level' => ReimbursementLevel::FREE]);

        $this->assertSame(ReimbursementLevel::FREE, $medicine->reimbursement_level);
    }

    /**
     * @return void
     */
    public function testPrescriptionsReturnsAttachedPrescriptionsWithPivotData(): void
    {
        $medicine = Medicine::factory()->create();
        $prescription = Prescription::factory()->create();

        $medicine->prescriptions()->attach($prescription->uuid, ['quantity' => 2, 'dosage' => '1 tabletka rano']);

        $attached = $medicine->prescriptions->first();
        $this->assertTrue($attached->is($prescription));
        $this->assertSame(2, $attached->pivot->quantity);
        $this->assertSame('1 tabletka rano', $attached->pivot->dosage);
    }
}
