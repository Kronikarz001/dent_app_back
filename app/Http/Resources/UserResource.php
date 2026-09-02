<?php

namespace App\Http\Resources;

use App\Enums\PhoneNumberType;

/**
 * Summary of UserResource
 */
class UserResource extends BasicResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'pesel' => $this->pesel,
            'private_phone_number' => $this->phoneNumbers->firstWhere('type', PhoneNumberType::PRIVATE->value)?->number,
            'phone_number' => $this->phoneNumbers->firstWhere('type', PhoneNumberType::WORK->value)?->number,
            'job_position' => new JobPositionResource($this->jobPosition),
            'avatar_path' => $this->avatar_path,
            'background_path' => $this->background_path,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'apartment_number' => $this->apartment_number,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'status' => $this->isOnline(),
        ]);
    }
}
