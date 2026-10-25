<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatusType;
use App\Notifications\ResetPasswordNotification;
use App\Traits\Auditable;
use App\Traits\HasFile;
use App\Traits\HasPhoneNumber;
use App\Traits\SerializesLocalDates;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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
 * @property-read string $gender
 * @property string|null $avatar_path
 * @property string|null $remember_token
 * @property string|null $status
 * @property string|null $job_position_uuid
 * @property string|null $street
 * @property string|null $house_number
 * @property string|null $apartment_number
 * @property string|null $postal_code
 * @property string|null $city
 * @property bool $is_admin
 * @property bool $is_superuser
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $private_email_verified_at
 * @property-read JobPosition|null $jobPosition
 * @property-read Collection|PhoneNumber[] $phoneNumbers
 * @property-read Collection|UserGroup[] $userGroups
 * @property-read Collection|Role[] $roles
 * @property-read Collection|Department[] $departments
 */
abstract class BasicUser extends Authenticatable
{
    public const RESOURCE_RELATIONS = ['phoneNumbers', 'jobPosition', 'latestAvatar', 'latestBackground', 'validTokens'];

    /** @use HasFactory<UserFactory> */
    use Auditable, HasApiTokens, HasFactory, HasFile, HasPhoneNumber, HasUuids, Notifiable, SerializesLocalDates;

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
        'status',
        'job_position_uuid',
        'street',
        'house_number',
        'apartment_number',
        'postal_code',
        'city',
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
     * @param BasicUser $user
     * @return static
     */
    public static function fromUser(BasicUser $user): static
    {
        return (new static)->newFromBuilder($user->getAttributes());
    }

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
        return $this->validTokens->isNotEmpty() ? UserStatusType::ACTIVE : UserStatusType::NON_ACTIVE;
    }

    /**
     * @return MorphMany
     */
    public function validTokens(): MorphMany
    {
        $expiration = config('sanctum.expiration');

        return $this->tokens()
            ->when($expiration, fn ($query) => $query->where('created_at', '>', now()->subMinutes($expiration)));
    }

    /**
     * @return HasOne
     */
    public function latestAvatar(): HasOne
    {
        return $this->latestFileOf(UserAvatar::class);
    }

    /**
     * @return HasOne
     */
    public function latestBackground(): HasOne
    {
        return $this->latestFileOf(UserBackground::class);
    }

    /**
     * @param string $fileableType
     * @return HasOne
     */
    private function latestFileOf(string $fileableType): HasOne
    {
        return $this->hasOne(File::class, 'fileable_id', 'uuid')
            ->where('fileable_type', $fileableType)
            ->where('is_latest', true)
            ->latest();
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
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return string|null
     */
    public function getGenderAttribute(): ?string
    {
        return Gender::fromPesel($this->pesel)?->code();
    }

    /**
     * @return string|null
     */
    public function getAvatarPathAttribute(): ?string
    {
        $file = $this->latestAvatar;

        return $file ? Storage::disk('public')->url($file->path) : null;
    }

    /**
     * @return string|null
     */
    public function getBackgroundPathAttribute(): ?string
    {
        $file = $this->latestBackground;

        return $file ? route('userfile.background-download', ['user' => $this->uuid, 'file' => $file->uuid]) : null;
    }

    /**
     * @return BelongsTo
     */
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'job_position_uuid', 'uuid');
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
