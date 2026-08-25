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
    public function assignPhone(Model $model, array $phones): void
    {
        $modelClass = get_class($model);

        foreach ($phones as $phone) {
            $existing = PhoneNumber::where('phoneable_type', $modelClass)
                ->where('phoneable_uuid', $model->uuid)
                ->where('type', $phone['type'])
                ->first();

            if ($existing) {
                $existing->update(['number' => $phone['number']]);

                continue;
            }

            PhoneNumber::updateOrCreate(
                ['number' => $phone['number']],
                [
                    'type' => $phone['type'],
                    'phoneable_type' => $modelClass,
                    'phoneable_uuid' => $model->uuid,
                ]
            );
        }
    }
}
