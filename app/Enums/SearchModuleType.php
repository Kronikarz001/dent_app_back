<?php

namespace App\Enums;

use App\Models\Announcement;
use App\Models\Calendar;
use App\Models\DentalExamination;
use App\Models\JobPosition;
use App\Models\Material;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Summary of SearchModuleType
 */
enum SearchModuleType: string
{
    case PATIENTS = 'patients';
    case USERS = 'users';
    case JOB_POSITIONS = 'job_positions';
    case MATERIALS = 'materials';
    case DENTAL_EXAMINATIONS = 'dental_examinations';
    case MESSAGES = 'messages';
    case ANNOUNCEMENTS = 'announcements';
    case CALENDARS = 'calendars';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PATIENTS => 'Pacjenci',
            self::USERS => 'Użytkownicy',
            self::JOB_POSITIONS => 'Stanowiska',
            self::MATERIALS => 'Materiały',
            self::DENTAL_EXAMINATIONS => 'Badania stomatologiczne',
            self::MESSAGES => 'Wiadomości',
            self::ANNOUNCEMENTS => 'Ogłoszenia',
            self::CALENDARS => 'Kalendarze',
        };
    }

    /**
     * @return class-string
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::PATIENTS => Patient::class,
            self::USERS => User::class,
            self::JOB_POSITIONS => JobPosition::class,
            self::MATERIALS => Material::class,
            self::DENTAL_EXAMINATIONS => DentalExamination::class,
            self::MESSAGES => Message::class,
            self::ANNOUNCEMENTS => Announcement::class,
            self::CALENDARS => Calendar::class,
        };
    }

    /**
     * @return string
     */
    public function nameExpression(): string
    {
        return match ($this) {
            self::PATIENTS, self::USERS => "concat_ws(' ', first_name, last_name)",
            self::ANNOUNCEMENTS => 'title',
            self::MESSAGES => 'message',
            default => 'name',
        };
    }

    /**
     * @return string
     */
    public function descriptionExpression(): string
    {
        return match ($this) {
            self::PATIENTS, self::USERS => 'email',
            self::MATERIALS, self::DENTAL_EXAMINATIONS => 'coalesce(short_description, description)',
            self::ANNOUNCEMENTS => 'content',
            self::CALENDARS => 'description',
            default => 'null',
        };
    }

    /**
     * @return bool
     */
    public function usesSoftDeletes(): bool
    {
        return $this === self::JOB_POSITIONS;
    }

    /**
     * @param string $uuid
     * @return string|null
     */
    public function showLinkForUuid(string $uuid): ?string
    {
        $routeName = $this->resolveShowRouteName();

        if ($routeName === null) {
            return null;
        }

        return route($routeName, [$uuid]);
    }

    /**
     * @return string|null
     */
    private function resolveShowRouteName(): ?string
    {
        $singular = Str::singular($this->value);

        $candidates = [
            Str::camel($singular).'.show',
            $singular.'.show',
            Str::kebab($singular).'.show',
        ];

        foreach ($candidates as $candidate) {
            if (Route::has($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (SearchModuleType $module): array => [
                'value' => $module->value,
                'label' => $module->label(),
            ],
            self::cases()
        );
    }
}
