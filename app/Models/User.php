<?php

namespace App\Models;

use App\Models\Concerns\HasFile;
use App\Models\Concerns\HasPhoneNumber;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Summary of User
 * @property string $uuid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $private_email
 * @property string $password
 * @property string $pesel
 * @property string|null $avatar_path
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $private_email_verified_at
 * @property-read Collection|JobPosition[] $jobPositions
 * @property-read Collection|PhoneNumber[] $phoneNumbers
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasApiTokens, HasPhoneNumber, HasFile;

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * Indicates if the IDs are auto-incrementing.
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass-assignable.
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'private_email',
        'password',
        'pesel',
        'is_active',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pesel',
        'remember_token',
    ];

    /**
     * @param string $token
     * @return User|null
     */
    public static function whereToken(string $token): ?self
    {
        $accessToken = PersonalAccessToken::findToken($token);

        if (is_null($accessToken)) {
            return null;
        }

        return $accessToken->tokenable;
    }

    /**
     * Get the attributes that should be cast.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'private_email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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
     * @return HasMany
     */
    public function jobPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class, 'user_uuid', 'uuid');
    }
}
