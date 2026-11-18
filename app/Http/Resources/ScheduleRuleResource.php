<?php

namespace App\Http\Resources;

use App\Models\ScheduleRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of ScheduleRuleResource
 *
 * @mixin ScheduleRule
 */
#[OA\Schema(
    schema: 'ScheduleRuleResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'user_uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'day', type: 'string', enum: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
        new OA\Property(property: 'start_time', type: 'string', example: '08:00:00'),
        new OA\Property(property: 'end_time', type: 'string', example: '16:00:00'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ScheduleRuleResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'user_uuid' => $this->user_uuid,
            'day' => $this->day,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
