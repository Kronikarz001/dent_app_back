<?php

namespace App\Models;

use App\Enums\PermissionType;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of Permission
 */
class Permission extends UuidModel
{
    protected $fillable = [
        'resource',
        'type',
    ];

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'type' => PermissionType::class,
        ];
    }

    /**
     * @return string
     */
    public function getNameAttribute(): string
    {
        return "{$this->resource}.{$this->type->value}";
    }

    /**
     * @return BelongsToMany
     */
    public function permissionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionGroup::class,
            'permission_group_permission',
            'permission_uuid',
            'permission_group_uuid',
            'uuid',
            'uuid'
        );
    }
}
