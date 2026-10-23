<?php

namespace App\Dto;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Summary of PatientStatisticsDto
 */
class PatientStatisticsDto extends Dto
{
    /**
     * @param PatientVisitDto|null $lastVisit
     * @param PatientVisitDto|null $nextVisit
     * @param int $noShowCount
     * @param int $totalAppointments
     * @param Carbon|null $lastNoShowAt
     * @param User|null $leadDoctor
     * @param string|null $leadDoctorSince
     */
    public function __construct(
        private readonly ?PatientVisitDto $lastVisit,
        private readonly ?PatientVisitDto $nextVisit,
        private readonly int $noShowCount,
        private readonly int $totalAppointments,
        private readonly ?Carbon $lastNoShowAt,
        private readonly ?User $leadDoctor,
        private readonly ?string $leadDoctorSince,
    ) {}

    /**
     * @return PatientVisitDto|null
     */
    public function getLastVisit(): ?PatientVisitDto
    {
        return $this->lastVisit;
    }

    /**
     * @return PatientVisitDto|null
     */
    public function getNextVisit(): ?PatientVisitDto
    {
        return $this->nextVisit;
    }

    /**
     * @return int
     */
    public function getNoShowCount(): int
    {
        return $this->noShowCount;
    }

    /**
     * @return int
     */
    public function getTotalAppointments(): int
    {
        return $this->totalAppointments;
    }

    /**
     * @return Carbon|null
     */
    public function getLastNoShowAt(): ?Carbon
    {
        return $this->lastNoShowAt;
    }

    /**
     * @return User|null
     */
    public function getLeadDoctor(): ?User
    {
        return $this->leadDoctor;
    }

    /**
     * @return string|null
     */
    public function getLeadDoctorSince(): ?string
    {
        return $this->leadDoctorSince;
    }
}
