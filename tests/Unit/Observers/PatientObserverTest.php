<?php

namespace Tests\Unit\Observers;

use App\Enums\Gender;
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

    /**
     * @return void
     */
    public function testSavingDerivesFemaleGenderFromEvenPeselDigit(): void
    {
        $patient = new Patient;
        $patient->pesel = '90010112345';

        (new PatientObserver)->saving($patient);

        $this->assertSame(Gender::FEMALE, $patient->gender);
    }

    /**
     * @return void
     */
    public function testSavingDerivesMaleGenderFromOddPeselDigit(): void
    {
        $patient = new Patient;
        $patient->pesel = '90010112335';

        (new PatientObserver)->saving($patient);

        $this->assertSame(Gender::MALE, $patient->gender);
    }

    /**
     * @return void
     */
    public function testSavingKeepsExplicitGenderWhenPeselUnchanged(): void
    {
        $patient = new Patient;
        $patient->setRawAttributes(['pesel' => '90010112345', 'gender' => Gender::MALE->value], true);

        (new PatientObserver)->saving($patient);

        $this->assertSame(Gender::MALE, $patient->gender);
    }

    /**
     * @return void
     */
    public function testSavingRederivesGenderWhenPeselChanges(): void
    {
        $patient = new Patient;
        $patient->setRawAttributes(['pesel' => '90010112345', 'gender' => Gender::FEMALE->value], true);
        $patient->pesel = '90010112335';

        (new PatientObserver)->saving($patient);

        $this->assertSame(Gender::MALE, $patient->gender);
    }

    /**
     * @return void
     */
    public function testSavingKeepsGenderWhenPeselIsInvalid(): void
    {
        $patient = new Patient;
        $patient->setRawAttributes(['pesel' => '90010112345', 'gender' => Gender::MALE->value], true);
        $patient->pesel = '123';

        (new PatientObserver)->saving($patient);

        $this->assertSame(Gender::MALE, $patient->gender);
    }
}
