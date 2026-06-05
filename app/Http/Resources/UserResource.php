<?php

namespace App\Http\Resources;

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
            'phone_number' => PhoneNumberResource::collection($this->phoneNumbers),
            'avatar_url' => $this->avatar_url,
            'background_url' => $this->background_url,
        ]);
    }
}
