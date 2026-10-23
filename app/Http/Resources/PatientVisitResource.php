<?php

namespace App\Http\Resources;

use App\Dto\PatientVisitDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of PatientVisitResource
 *
 * @property PatientVisitDto $resource
 */
#[OA\Schema(
    schema: 'PatientVisitResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'treatments', type: 'array', items: new OA\Items(ref: '#/components/schemas/DentalExaminationResource')),
        new OA\Property(property: 'date', type: 'string', example: '2026-10-02T12:00:00.000000'),
        new OA\Property(property: 'doctor', ref: '#/components/schemas/DoctorResource', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['COMPLETED', 'NO_SHOW', 'SCHEDULED']),
    ]
)]
class PatientVisitResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->getUuid(),
            'treatments' => DentalExaminationResource::collection($this->resource->getTreatments()),
            'date' => $this->resource->getDate(),
            'doctor' => $this->resource->getDoctor() ? new DoctorResource($this->resource->getDoctor()) : null,
            'status' => $this->resource->getStatus(),
        ];
    }
}
