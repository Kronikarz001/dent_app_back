<?php

namespace Tests\Unit\Observers;

use App\Models\Patient;
use App\Observers\PatientObserver;
use Tests\Unit\UnitTestCase;

/**
 * Summary of PatientObserverTest
 */
class PatientObserverTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatingSetsIsActiveToTrue(): void
    {
        $patient = new Patient;

        (new PatientObserver)->creating($patient);

        $this->assertTrue($patient->is_active);
    }

    /**
     * @return void
     */
    public function testCreatingDoesNotOverwriteExplicitIsActive(): void
    {
        $patient = new Patient;
        $patient->is_active = false;

        (new PatientObserver)->creating($patient);

        $this->assertTrue($patient->is_active);
    }
}
