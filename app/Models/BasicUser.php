<?php

namespace App\Models;

use App\Traits\HasFile;
use App\Traits\HasPhoneNumber;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property string $uuid
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $private_email
 * @property string $password
 * @property string $pesel
 * @property string|null $avatar_path
 * @property string|null $pwz_numer
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $private_email_verified_at
 * @property-read Collection|JobPosition[] $jobPositions
 * @property-read Collection|PhoneNumber[] $phoneNumbers
 */
abstract class BasicUser extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasFile, HasPhoneNumber, HasUuids, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
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
        'pwz_numer',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pesel',
        'remember_token',
    ];

    /**
     * @param string $token
     * @return static|null
     */
    public static function whereToken(string $token): ?static
    {
        $accessToken = PersonalAccessToken::findToken($token);

        if (is_null($accessToken)) {
            return null;
        }

        return $accessToken->tokenable;
    }

    /**
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
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * @return string|null
     */
    public function getAvatarPathAttribute(): ?string
    {
        $file = File::where('fileable_type', UserAvatar::class)
            ->where('fileable_id', $this->uuid)
            ->where('is_latest', true)
            ->latest()
            ->first();

        if (! $file) {
            return null;
        }

        return route('userfile.avatar-download', ['user' => $this->uuid, 'file' => $file->uuid]);
    }

    /**
     * @return string|null
     */
    public function getBackgroundPathAttribute(): ?string
    {
        $file = File::where('fileable_type', UserBackground::class)
            ->where('fileable_id', $this->uuid)
            ->where('is_latest', true)
            ->latest()
            ->first();

        if (! $file) {
            return null;
        }

        return route('userfile.background-download', ['user' => $this->uuid, 'file' => $file->uuid]);
    }

    /**
     * @return BelongsToMany
     */
    public function jobPositions(): BelongsToMany
    {
        return $this->belongsToMany(
            JobPosition::class,
            'users_job_positions',
            'user_uuid',
            'job_position_uuid',
            'uuid',
            'uuid'
        );
    }

    /**
     * @return MorphToMany
     */
    public function calendars(): MorphToMany
    {
        return $this->morphToMany(Calendar::class, 'userable', 'calendar_users');
    }
}
