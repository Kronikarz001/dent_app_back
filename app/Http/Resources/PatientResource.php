<?php

namespace App\Http\Resources;

/**
 * Summary of UserResource
 */
class PatientResource extends BasicResource
{
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'phone_number' => PhoneNumberResource::collection($this->phoneNumbers),
        ]);
    }
}
