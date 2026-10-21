<?php

namespace App\Http\Resources;

use App\Dto\PatientStatisticsDto;
use App\Models\BasicUser;
use App\Models\Calendar;
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
        $leadDoctor = $this->resource->getLeadDoctor();

        return [
            'last_visit' => $this->visit($this->resource->getLastVisit()),
            'next_visit' => $this->visit($this->resource->getNextVisit()),
            'no_shows' => [
                'count' => $this->resource->getNoShowCount(),
                'total_appointments' => $this->resource->getTotalAppointments(),
                'last_at' => $this->resource->getLastNoShow()?->startsAt(),
            ],
            'lead_doctor' => $leadDoctor === null ? null : [
                'doctor' => $this->doctor($leadDoctor),
                'since' => $this->resource->getLeadDoctorSince()?->toDateString(),
            ],
        ];
    }

    /**
     * @param Calendar|null $calendar
     * @return array|null
     */
    private function visit(?Calendar $calendar): ?array
    {
        if ($calendar === null) {
            return null;
        }

        $doctor = $calendar->users->first();

        return [
            'uuid' => $calendar->uuid,
            'starts_at' => $calendar->startsAt(),
            'doctor' => $doctor === null ? null : $this->doctor($doctor),
        ];
    }

    /**
     * @param BasicUser $doctor
     * @return array
     */
    private function doctor(BasicUser $doctor): array
    {
        return [
            'uuid' => $doctor->uuid,
            'first_name' => $doctor->first_name,
            'last_name' => $doctor->last_name,
            'position' => $doctor->jobPosition?->name,
        ];
    }
}
