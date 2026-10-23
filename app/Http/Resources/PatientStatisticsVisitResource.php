<?php

namespace App\Http\Resources;

use App\Dto\PatientVisitDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Summary of PatientStatisticsVisitResource
 *
 * @property PatientVisitDto $resource
 */
class PatientStatisticsVisitResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->getUuid(),
            'starts_at' => $this->resource->getDate(),
            'doctor' => $this->resource->getDoctor() ? new DoctorResource($this->resource->getDoctor()) : null,
        ];
    }
}
