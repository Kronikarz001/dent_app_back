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
        );
    }
}
