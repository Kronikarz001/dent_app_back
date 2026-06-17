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
            'avatar_path' => $this->avatar_path,
            'background_path' => $this->background_path,
        ]);
    }
}
