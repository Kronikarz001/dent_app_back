<?php

namespace App\Models;

use App\Enums\Gender;
use App\Observers\PatientObserver;
use App\Traits\Auditable;
use App\Traits\HasFile;
use App\Traits\HasPhoneNumber;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * Summary of JobPosition
 *
 * @property string $uuid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $pesel
 * @property bool $is_active
 * @property string|null $street
 * @property string|null $house_number
 * @property string|null $apartment_number
 * @property string|null $postal_code
 * @property string|null $city
 * @property Gender|null $gender
 * @property string|null $notes
 * @property string|null $doctor_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $doctor
 */
#[ObservedBy(PatientObserver::class)]
class Patient extends UuidModel
{
    use Auditable, HasFile, HasPhoneNumber;

    /**
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'pesel',
        'is_active',
        'street',
        'house_number',
        'apartment_number',
        'postal_code',
        'city',
        'gender',
        'notes',
        'doctor_uuid',
    ];

    /**
     * @return string
     */
    public function getName(): string
    {
        $firstName = $this->firstName;
        $lastName = $this->lastName;

        return "$firstName $lastName";
    }

    /**
     * @return MorphToMany
     */
    public function calendars(): MorphToMany
    {
        return $this->morphToMany(Calendar::class, 'userable', 'calendar_users');
    }

    /**
     * @return BelongsTo
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_uuid', 'uuid');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
        ];
    }
}
