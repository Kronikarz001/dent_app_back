<?php

namespace App\Traits;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Summary of HasPhoneNumber
 */
trait HasPhoneNumber
{
    /**
     * @return MorphMany
     */
    public function phoneNumbers(): MorphMany
    {
        return $this->morphMany(PhoneNumber::class, 'phoneable');
    }
}
