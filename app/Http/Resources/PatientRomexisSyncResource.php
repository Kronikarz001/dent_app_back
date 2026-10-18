<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Summary of PatientRomexisSyncResource
 */
class PatientRomexisSyncResource extends BasicResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'romexis_patient_id' => $this->romexis_patient_id,
        ];
    }
}
