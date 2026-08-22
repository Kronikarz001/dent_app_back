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
            'private_phone_number' => $this->phoneNumbers->firstWhere('type', PhoneNumberType::PRIVATE->value)?->number,
            'phone_number' => $this->phoneNumbers->firstWhere('type', PhoneNumberType::WORK->value)?->number,
            'job_positions' => JobPositionResource::collection($this->jobPositions),
            'avatar_path' => $this->avatar_path,
            'background_path' => $this->background_path,
            'status' => $this->isOnline(),
        ]);
    }
}
