<?php

namespace App\Observers;

use App\Enums\Gender;
use App\Models\Patient;

/**
 * Summary of PatientObserver
 */
class PatientObserver
{
    /**
     * @param Patient $patient
     * @return void
     */
    public function creating(Patient $patient): void
    {
        $patient->is_active = true;
    }

    /**
     * @param Patient $patient
     * @return void
     */
    public function saving(Patient $patient): void
    {
        if (! blank($patient->gender) && ! $patient->isDirty('pesel')) {
            return;
        }

        $gender = Gender::fromPesel($patient->pesel);

        if ($gender !== null) {
            $patient->gender = $gender;
        }
    }
}
