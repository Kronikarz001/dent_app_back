<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Summary of PermissionGroup
 */
class PermissionGroup extends UuidModel
{
    use Auditable;

    protected $fillable = [
        'name',
        'description',
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

    /**
     * @return MorphToMany
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            User::class,
            'assignable',
            'permission_assignments',
            'grantable_id',
            'assignable_id',
            'uuid',
            'uuid'
        )
            ->wherePivot('grantable_type', self::class)
            ->where(fn (Builder $query) => $query
                ->whereNull('permission_assignments.expires_at')
                ->orWhere('permission_assignments.expires_at', '>', now()));
    }
}
