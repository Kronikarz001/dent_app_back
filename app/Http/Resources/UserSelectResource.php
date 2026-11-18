<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of UserSelectResource
 *
 * @mixin User
 */
#[OA\Schema(
    schema: 'UserSelectResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'first_name', type: 'string'),
        new OA\Property(property: 'last_name', type: 'string'),
        new OA\Property(property: 'avatar_path', type: 'string', nullable: true),
        new OA\Property(property: 'job_position', ref: '#/components/schemas/SelectListItem', nullable: true),
    ]
)]
class UserSelectResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'avatar_path' => $this->avatar_path,
            'job_position' => $this->jobPosition ? [
                'uuid' => $this->jobPosition->uuid,
                'name' => $this->jobPosition->name,
            ] : null,
        ];
    }
}
