<?php

namespace App\Enums;

use App\Search\AnnouncementSearch;
use App\Search\CalendarSearch;
use App\Search\DentalExaminationSearch;
use App\Search\JobPositionSearch;
use App\Search\MaterialSearch;
use App\Search\MessageSearch;
use App\Search\PatientSearch;
use App\Search\UserSearch;
use Illuminate\Database\Eloquent\Model;
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
    public function searchClass(): string
    {
        return match ($this) {
            self::PATIENTS => PatientSearch::class,
            self::USERS => UserSearch::class,
            self::JOB_POSITIONS => JobPositionSearch::class,
            self::MATERIALS => MaterialSearch::class,
            self::DENTAL_EXAMINATIONS => DentalExaminationSearch::class,
            self::MESSAGES => MessageSearch::class,
            self::ANNOUNCEMENTS => AnnouncementSearch::class,
            self::CALENDARS => CalendarSearch::class,
        };
    }

    /**
     * @param Model $model
     * @return string
     */
    public function resolveName(Model $model): string
    {
        return match ($this) {
            self::PATIENTS, self::USERS => trim(($model->first_name ?? '').' '.($model->last_name ?? '')),
            self::ANNOUNCEMENTS => (string) $model->title,
            self::MESSAGES => (string) $model->message,
            default => (string) $model->name,
        };
    }

    /**
     * @param Model $model
     * @return string|null
     */
    public function resolveDescription(Model $model): ?string
    {
        return match ($this) {
            self::PATIENTS, self::USERS => $model->email,
            self::MATERIALS, self::DENTAL_EXAMINATIONS => $model->short_description ?? $model->description,
            self::ANNOUNCEMENTS => $model->content,
            self::CALENDARS => $model->description,
            default => null,
        };
    }

    /**
     * @param Model $model
     * @return string|null
     */
    public function showLink(Model $model): ?string
    {
        $routeName = $this->resolveShowRouteName();

        if ($routeName === null) {
            return null;
        }

        return route($routeName, [$model->uuid]);
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
