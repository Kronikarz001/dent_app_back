<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Summary of PrescriptionResource
 */
class PrescriptionResource extends BasicResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'patient' => $this->whenLoaded('patient', fn () => new PatientResource($this->patient)),
            'doctor' => $this->whenLoaded('doctor', fn () => new UserResource($this->doctor)),
            'medicines' => $this->whenLoaded('medicines', fn () => $this->medicines->map(fn ($medicine) => array_merge(
                (new MedicineResource($medicine))->resolve($request),
                [
                    'quantity' => $medicine->pivot->quantity,
                    'dosage' => $medicine->pivot->dosage,
                ]
            ))),
        ]);
    }
}
