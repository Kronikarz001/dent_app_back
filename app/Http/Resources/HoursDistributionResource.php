<?php

namespace App\Http\Resources;

use App\Dto\HoursDistributionDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of HoursDistributionResource
 *
 * @property HoursDistributionDto $resource
 */
#[OA\Schema(
    schema: 'HoursDistributionResource',
    properties: [
        new OA\Property(property: 'type', type: 'string', enum: ['praca', 'l4', 'urlop']),
        new OA\Property(property: 'hours', type: 'number', format: 'float', example: 160),
        new OA\Property(property: 'percent', type: 'number', format: 'float', example: 86.96),
    ]
)]
class HoursDistributionResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->resource->getType(),
            'hours' => $this->resource->getHours(),
            'percent' => $this->resource->getPercent(),
        ];
    }
}
