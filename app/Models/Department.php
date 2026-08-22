<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Summary of Department
 */
class Department extends UuidModel
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
            'department_role',
            'department_uuid',
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
            'department_user',
            'department_uuid',
            'user_uuid',
            'uuid',
            'uuid'
        )->withPivot('is_manager');
    }

    /**
     * @return BelongsToMany
     */
    public function jobPositions(): BelongsToMany
    {
        return $this->belongsToMany(
            JobPosition::class,
            'department_job_position',
            'department_uuid',
            'job_position_uuid',
            'uuid',
            'uuid'
        );
    }
}
