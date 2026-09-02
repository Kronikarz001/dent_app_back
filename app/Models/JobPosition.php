<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Summary of JobPosition
 *
 * @property string $uuid
 * @property string $name
 * @property string $f_name
 * @property string $m_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class JobPosition extends UuidModel
{
    /**
     * @use SoftDeletes
     */
    use Auditable, HasFile, SoftDeletes;

    protected $table = 'job_positions';

    /**
     * @var string
     */
    public const DENTIST_NAME = 'Stanowisko lekarza stomatologa';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'f_name',
        'm_name',
    ];

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'job_position_uuid', 'uuid');
    }

    /**
     * @return BelongsToMany
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'department_job_position',
            'job_position_uuid',
            'department_uuid',
            'uuid',
            'uuid'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            UserGroup::class,
            'user_group_job_position',
            'job_position_uuid',
            'user_group_uuid',
            'uuid',
            'uuid'
        );
    }
}
