<?php

namespace App\Dto;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Summary of PatientStatisticsDto
 */
class PatientStatisticsDto extends Dto
{
    /**
     * @param Calendar|null $lastVisit
     * @param Calendar|null $nextVisit
     * @param int $noShowCount
     * @param int $totalAppointments
     * @param Calendar|null $lastNoShow
     * @param User|null $leadDoctor
     * @param Carbon|null $leadDoctorSince
     */
    public function __construct(
        private readonly ?Calendar $lastVisit,
        private readonly ?Calendar $nextVisit,
        private readonly int $noShowCount,
        private readonly int $totalAppointments,
        private readonly ?Calendar $lastNoShow,
        private readonly ?User $leadDoctor,
        private readonly ?Carbon $leadDoctorSince,
    ) {}

    /**
     * @return Calendar|null
     */
    public function getLastVisit(): ?Calendar
    {
        return $this->lastVisit;
    }

    /**
     * @return Calendar|null
     */
    public function getNextVisit(): ?Calendar
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
     * @return Calendar|null
     */
    public function getLastNoShow(): ?Calendar
    {
        return $this->lastNoShow;
    }

    /**
     * @return User|null
     */
    public function getLeadDoctor(): ?User
    {
        return $this->leadDoctor;
    }

    /**
     * @return Carbon|null
     */
    public function getLeadDoctorSince(): ?Carbon
    {
        return $this->leadDoctorSince;
    }
}
