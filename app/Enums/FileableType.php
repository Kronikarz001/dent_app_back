<?php

namespace App\Enums;

use App\Exceptions\FileableTypeException;
use App\Models\DentalExamination;
use App\Models\JobPosition;
use App\Models\Material;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserAvatar;
use App\Models\UserBackground;

/**
 * Summary of FileableType
 */
enum FileableType: string
{
    case USER = User::class;
    case PATIENT = Patient::class;
    case JOB_POSITION = JobPosition::class;
    case USER_AVATAR = UserAvatar::class;
    case USER_BACKGROUND = UserBackground::class;
    case DENTAL_EXAMINATION = DentalExamination::class;
    case MATERIAL = Material::class;
    case MESSAGE = Message::class;

    /**
     * @param string $name
     * @return FileableType
     *
     * @throws FileableTypeException
     */
    final public static function map(string $name): FileableType
    {
        foreach (self::cases() as $case) {
            if ($case->name == $name) {
                return $case;
            }
        }

        throw new FileableTypeException;
    }

    /**
     * @return string
     */
    final public function getDefaultDirectory(): string
    {
        return match ($this) {
            self::USER => 'user',
            self::PATIENT => 'patient',
            self::JOB_POSITION => 'job_position',
            self::USER_AVATAR => 'user_avatar',
            self::USER_BACKGROUND => 'user_background',
            self::DENTAL_EXAMINATION => 'dental_examination',
            self::MATERIAL => 'material',
            self::MESSAGE => 'message',
        };
    }
}
