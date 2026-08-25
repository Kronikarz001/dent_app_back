<?php

namespace App\Models;

use App\Enums\UserStatusType;
use App\Notifications\ResetPasswordNotification;
use App\Traits\Auditable;
use App\Traits\HasFile;
use App\Traits\HasPhoneNumber;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
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
 * @property string|null $status
 * @property bool $is_admin
 * @property bool $is_superuser
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $private_email_verified_at
 * @property-read Collection|JobPosition[] $jobPositions
 * @property-read Collection|PhoneNumber[] $phoneNumbers
 * @property-read Collection|UserGroup[] $userGroups
 * @property-read Collection|Role[] $roles
 * @property-read Collection|Department[] $departments
 */
abstract class BasicUser extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasApiTokens, HasFactory, HasFile, HasPhoneNumber, HasUuids, Notifiable;

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
        'is_admin',
        'is_superuser',
        'avatar_path',
        'pwz_numer',
        'status',
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
     * @return UserStatusType
     */
    public function isOnline(): UserStatusType
    {
        $expiration = config('sanctum.expiration');

        $hasValidToken = $this->tokens()
            ->when($expiration, fn ($query) => $query->where('created_at', '>', now()->subMinutes($expiration)))
            ->exists();

        return $hasValidToken ? UserStatusType::ACTIVE : UserStatusType::NON_ACTIVE;
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
            'is_admin' => 'boolean',
            'is_superuser' => 'boolean',
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

        return URL::temporarySignedRoute('userfile.avatar-download-signed', now()->addMinutes(60), ['user' => $this->uuid, 'file' => $file->uuid]);
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

        return URL::temporarySignedRoute('userfile.background-download-signed', now()->addMinutes(60), ['user' => $this->uuid, 'file' => $file->uuid]);
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

    /**
     * @return MorphMany
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return BelongsToMany
     */
    public function messageGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            MessageGroup::class,
            'message_group_users',
            'user_uuid',
            'message_group_uuid',
            'uuid',
            'uuid'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGroup::class,
            'user_group_user',
            'user_uuid',
            'user_group_uuid',
            'uuid',
            'uuid'
        )->withPivot('is_manager');
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_user',
            'user_uuid',
            'role_uuid',
            'uuid',
            'uuid'
        )->withPivot('is_manager');
    }

    /**
     * @return BelongsToMany
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'department_user',
            'user_uuid',
            'department_uuid',
            'uuid',
            'uuid'
        )->withPivot('is_manager');
    }
}
