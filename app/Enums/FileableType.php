<?php

namespace App\Enums;

use App\Exceptions\FileableTypeException;
use App\Models\JobPosition;
use App\Models\Patient;
use App\Models\User;

/**
 * Summary of FileableType
 */
enum FileableType: string
{
    case USER = User::class;
    case PATIENT = Patient::class;
    case JOB_POSITION = JobPosition::class;

    /**
     * @param string $name
     * @return FileableType
     * @throws FileableTypeException
     */
    public final static function map(string $name): FileableType
    {
        foreach (self::cases() as $case) {
            if ($case->name == $name) {
                return $case;
            }
        }

        throw new FileableTypeException();
    }

    /**
     * @return string
     */
    public final function getDefaultDirectory(): string
    {
        return match ($this) {
            self::USER => 'user',
            self::PATIENT => 'patient',
            self::JOB_POSITION => 'job_position',
        };
    }
}
