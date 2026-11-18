<?php

namespace App\Dto;

use App\Enums\VisitStatus;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Summary of PatientVisitDto
 */
class PatientVisitDto extends Dto
{
    /**
     * @param string $uuid
     * @param Carbon $date
     * @param User|null $doctor
     * @param Collection $treatments
     * @param VisitStatus $status
     */
    public function __construct(
        private readonly string $uuid,
        private readonly Carbon $date,
        private readonly ?User $doctor,
        private readonly Collection $treatments,
        private readonly VisitStatus $status,
    ) {}

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @return User|null
     */
    public function getDoctor(): ?User
    {
        return $this->doctor;
    }

    /**
     * @return Collection
     */
    public function getTreatments(): Collection
    {
        return $this->treatments;
    }

    /**
     * @return VisitStatus
     */
    public function getStatus(): VisitStatus
    {
        return $this->status;
    }
}
