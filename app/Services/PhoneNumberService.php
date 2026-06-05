<?php

namespace App\Services;

use App\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PhoneNumberService
 */
class PhoneNumberService implements PhoneNumberServiceInterface
{
    /**
     * @param Model $model
     * @param array $phones
     * @return void
     */
    public function assignPhones(Model $model, array $phones): void
    {
        $modelClass = get_class($model);
        $records = array_map(fn (array $phone) => [
            'number' => $phone['number'],
            'type' => $phone['type'],
            'phoneable_type' => $modelClass,
            'phoneable_id' => $model->uuid,
        ], $phones);

        PhoneNumber::upsert($records, ['number'], ['type']);
    }
}
