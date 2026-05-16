<?php

namespace App\Models\Concerns;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPhoneNumber
{
    /**
     * @return MorphMany
     */
    public function phoneNumbers(): MorphMany
    {
        return $this->morphMany(PhoneNumber::class, 'phoneable', 'phoneable_type', 'phoneable_id', 'uuid');
    }
}
