<?php

namespace App\Http\Resources;

use App\Dto\StatisticUserDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of StatisticUserResource
 *
 * @property StatisticUserDto $resource
 */
#[OA\Schema(
    schema: 'StatisticUserResource',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/UserSelectResource'),
        new OA\Property(property: 'work_hours', type: 'number', format: 'float', example: 160),
        new OA\Property(property: 'vacation_hours', type: 'number', format: 'float', example: 16),
        new OA\Property(property: 'sick_leave_hours', type: 'number', format: 'float', example: 8),
    ]
)]
class StatisticUserResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserSelectResource($this->resource->getUser()),
            'work_hours' => $this->resource->getSummary()->getWorkHours(),
            'vacation_hours' => $this->resource->getSummary()->getVacationHours(),
            'sick_leave_hours' => $this->resource->getSummary()->getSickLeaveHours(),
        ];
    }
}
