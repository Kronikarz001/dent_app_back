<?php

namespace App\Services;

use App\Exceptions\PhoneNumberAlreadyAssignedException;
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
     *
     * @throws PhoneNumberAlreadyAssignedException
     */
    public function assignPhone(Model $model, array $phones): void
    {
        $modelClass = get_class($model);

        foreach ($phones as $phone) {
            if (blank($phone['number'] ?? null)) {
                $this->removePhone($modelClass, $model->uuid, $phone['type']);

                continue;
            }

            $this->assertNumberNotOwnedByAnotherEntity($modelClass, $model->uuid, $phone['number']);

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

    /**
     * @param string $modelClass
     * @param string $modelUuid
     * @param string $type
     * @return void
     */
    private function removePhone(string $modelClass, string $modelUuid, string $type): void
    {
        PhoneNumber::where('phoneable_type', $modelClass)
            ->where('phoneable_uuid', $modelUuid)
            ->where('type', $type)
            ->delete();
    }

    /**
     * @param string $modelClass
     * @param string $modelUuid
     * @param string $number
     * @return void
     *
     * @throws PhoneNumberAlreadyAssignedException
     */
    private function assertNumberNotOwnedByAnotherEntity(string $modelClass, string $modelUuid, string $number): void
    {
        $ownedByOther = PhoneNumber::where('number', $number)
            ->where(function ($query) use ($modelClass, $modelUuid) {
                $query->where('phoneable_type', '!=', $modelClass)
                    ->orWhere('phoneable_uuid', '!=', $modelUuid);
            })
            ->exists();

        if ($ownedByOther) {
            throw new PhoneNumberAlreadyAssignedException;
        }
    }
}
