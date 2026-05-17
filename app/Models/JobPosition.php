<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Summary of JobPosition
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
    use SoftDeletes, HasFile;

    protected $table = 'job_positions';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'f_name',
        'm_name',
    ];

    /**
     * @return BelongsToMany
     */
    public function user(): BelongsToMany
    {
        return $this->BelongsToMany(User::class, 'users_job_positions', 'job_position_uuid', 'user_uuid');
    }
}
