<?php

namespace App\Models;

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
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'f_name',
        'm_name',
    ];
}
