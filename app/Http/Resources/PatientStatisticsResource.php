<?php

namespace App\Http\Resources;

use App\Dto\PatientStatisticsDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Summary of PatientStatisticsResource
 *
 * @property PatientStatisticsDto $resource
 */
class PatientStatisticsResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'last_visit' => $this->resource->getLastVisit() ? new PatientStatisticsVisitResource($this->resource->getLastVisit()) : null,
            'next_visit' => $this->resource->getNextVisit() ? new PatientStatisticsVisitResource($this->resource->getNextVisit()) : null,
            'no_shows' => [
                'count' => $this->resource->getNoShowCount(),
                'total_appointments' => $this->resource->getTotalAppointments(),
                'last_at' => $this->resource->getLastNoShowAt(),
            ],
            'lead_doctor' => $this->resource->getLeadDoctor() ? [
                'doctor' => new DoctorResource($this->resource->getLeadDoctor()),
                'since' => $this->resource->getLeadDoctorSince(),
            ] : null,
        ];
    }
}
