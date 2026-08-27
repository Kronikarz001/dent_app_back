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
            $this->assertNumberNotOwnedByAnotherEntity($modelClass, $model->uuid, $phone['number']);

            $existing = PhoneNumber::where('phoneable_type', $modelClass)
                ->where('phoneable_uuid', $model->uuid)
                ->where('type', $phone['type'])
                ->first();

            if ($existing) {
                $existing->update(['number' => $phone['number']]);

                continue;
            }

            // Guard above already rules out the "owned by someone else"
            // case, so a match here (by number, not owner+type — see the
            // check above) can only be this same owner's row under a
            // different type, which is exactly what should be renamed.
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
     * `number` is unique system-wide (not just per owner) — without this
     * check, assigning a number already held by a different user/patient
     * would silently reassign that row's phoneable_type/phoneable_uuid to
     * the new owner instead of rejecting the request.
     *
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
