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
    public function test_creating_sets_is_active_to_true(): void
    {
        $patient = new Patient;

        (new PatientObserver)->creating($patient);

        $this->assertTrue($patient->is_active);
    }

    /**
     * @return void
     */
    public function test_creating_does_not_overwrite_explicit_is_active(): void
    {
        $patient = new Patient;
        $patient->is_active = false;

        (new PatientObserver)->creating($patient);

        $this->assertTrue($patient->is_active);
    }
}
