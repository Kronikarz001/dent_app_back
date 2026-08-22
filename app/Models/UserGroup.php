<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of UserGroup
 */
class UserGroup extends UuidModel
{
    use Auditable;

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_group_user',
            'user_group_uuid',
            'user_uuid',
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
            'user_group_role',
            'user_group_uuid',
            'role_uuid',
            'uuid',
            'uuid'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function jobPositions(): BelongsToMany
    {
        return $this->belongsToMany(
            JobPosition::class,
            'user_group_job_position',
            'user_group_uuid',
            'job_position_uuid',
            'uuid',
            'uuid'
        );
    }
}
