<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of RoleGroup
 */
class RoleGroup extends UuidModel
{
    use Auditable;

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_group_role',
            'role_group_uuid',
            'role_uuid',
            'uuid',
            'uuid'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'role_group_user',
            'role_group_uuid',
            'user_uuid',
            'uuid',
            'uuid'
        )->withPivot('is_manager');
    }
}
