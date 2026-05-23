<?php

namespace App\Observers;

use App\Models\Patient;

/**
 * Summary of PatientObserver
 */
class PatientObserver
{
    /**
     * @param  Patient  $patient
     * @return void
     */
    public function creating(Patient $patient): void
    {
        $patient->is_active = true;
    }
}
