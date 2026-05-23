<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhoneNumberServiceInterface
 */
interface PhoneNumberServiceInterface
{
    public function assignPhones(Model $model, array $phones): void;
}
