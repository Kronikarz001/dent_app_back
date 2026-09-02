<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Summary of Dentist
 */
class Dentist extends User
{
    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope('dentist', function (Builder $query) {
            $query->whereHas('jobPosition', function (Builder $q) {
                $q->where('name', JobPosition::DENTIST_NAME);
            });
        });
    }
}
