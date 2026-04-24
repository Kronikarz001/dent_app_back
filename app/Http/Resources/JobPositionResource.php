<?php

namespace App\Http\Resources;

/**
 * Summary of JobPositionResource
 */
class JobPositionResource extends BasicResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'phone_number' => PhoneNumberResource::collection($this->phoneNumbers)
        ]);
    }
}
