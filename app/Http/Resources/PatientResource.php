<?php

namespace App\Http\Resources;

/**
 * Summary of UserResource
 */
class PatientResource extends BasicResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'phone_number' => PhoneNumberResource::collection($this->phoneNumbers),
            'street' => $this->street,
            'house_number' => $this->house_number,
            'apartment_number' => $this->apartment_number,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'gender' => $this->gender,
            'notes' => $this->notes,
            'doctor' => $this->doctor ? new UserResource($this->doctor) : null,
        ]);
    }
}
