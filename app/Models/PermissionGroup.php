<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of PermissionGroup
 */
class PermissionGroup extends UuidModel
{
    use Auditable;

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_group_permission',
            'permission_group_uuid',
            'permission_uuid',
            'uuid',
            'uuid'
        );
    }
}
