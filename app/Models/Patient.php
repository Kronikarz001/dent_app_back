<?php

namespace App\Models;

use App\Observers\PatientObserver;
use App\Traits\Auditable;
use App\Traits\HasFile;
use App\Traits\HasPhoneNumber;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
}
