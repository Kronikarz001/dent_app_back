<?php

namespace App\Enums;

use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\JobPosition;
use App\Models\Material;
use App\Models\Patient;
use App\Models\User;

/**
 * Summary of AuditableType
 */
enum AuditableType: string
{
    case USER = 'user';
    case PATIENT = 'patient';
    case CALENDAR = 'calendar';
    case DENTAL_EXAMINATION = 'dental-examination';
    case MATERIAL = 'material';
    case JOB_POSITION = 'job-position';

    /**
     * @return string
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::USER => User::class,
            self::PATIENT => Patient::class,
            self::CALENDAR => Calendar::class,
            self::DENTAL_EXAMINATION => DentalExamination::class,
            self::MATERIAL => Material::class,
            self::JOB_POSITION => JobPosition::class,
        };
    }
}
