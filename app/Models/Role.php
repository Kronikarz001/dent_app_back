<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of Role
 */
class Role extends UuidModel
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
            'role_user',
            'role_uuid',
            'user_uuid',
            'uuid',
            'uuid'
        )->withPivot('is_manager');
    }

    /**
     * @return BelongsToMany
     */
    public function roleGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            RoleGroup::class,
            'role_group_role',
            'role_uuid',
            'role_group_uuid',
            'uuid',
            'uuid'
        );
    }
}
