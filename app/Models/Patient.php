<?php

namespace App\Models;

use App\Observers\PatientObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy(PatientObserver::class)]
class Patient extends UuidModel
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'pesel',
        'is_active',
    ];

    public function getName(): string
    {
        $firstName = $this->firstName;
        $lastName = $this->lastName;

        return "$firstName $lastName";
    }

    public function phoneNumbers(): MorphMany
    {
        return $this->morphMany(PhoneNumber::class, 'phoneable', 'phoneable_type', 'phoneable_id', 'uuid');
    }
}
