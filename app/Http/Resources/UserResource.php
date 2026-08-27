<?php

namespace App\Http\Resources;

use App\Enums\PhoneNumberType;

/**
 * Summary of UserResource
 */
class UserResource extends BasicResource
{
    /**
     * Explicit whitelist (not the inherited default toArray()) — a view of
     * another user must never leak admin/superuser flags to every coworker
     * with plain "user.view" permission, regardless of what columns the
     * users table grows in the future.
     *
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'private_email' => $this->private_email,
            'is_active' => $this->is_active,
            'pwz_numer' => $this->pwz_numer,
            'avatar_path' => $this->avatar_path,
            'background_path' => $this->background_path,
            'private_phone_number' => $this->phoneNumbers->firstWhere('type', PhoneNumberType::PRIVATE->value)?->number,
            'phone_number' => $this->phoneNumbers->firstWhere('type', PhoneNumberType::WORK->value)?->number,
            'job_positions' => JobPositionResource::collection($this->jobPositions),
            'status' => $this->isOnline(),
            'created_at' => $this->created_at,
        ];
    }
}
