<?php

namespace App\Http\Resources;

use App\Dto\HoursSummaryDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of HoursSummaryResource
 *
 * @property HoursSummaryDto $resource
 */
#[OA\Schema(
    schema: 'HoursSummaryResource',
    properties: [
        new OA\Property(property: 'praca', type: 'number', format: 'float', example: 160),
        new OA\Property(property: 'l4', type: 'number', format: 'float', example: 8),
        new OA\Property(property: 'urlop', type: 'number', format: 'float', example: 16),
    ]
)]
class HoursSummaryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'praca' => $this->resource->getWorkHours(),
            'l4' => $this->resource->getSickLeaveHours(),
            'urlop' => $this->resource->getVacationHours(),
        ];
    }
}
