<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhoneNumberServiceInterface
 */
interface PhoneNumberServiceInterface
{
    /**
     * @param Model $model
     * @param array $phones
     * @return void
     */
    public function assignPhone(Model $model, array $phones): void;
}
