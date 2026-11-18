<?php

namespace App\Http\Resources;

use App\Dto\DailyHoursDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of DailyHoursResource
 *
 * @property DailyHoursDto $resource
 */
#[OA\Schema(
    schema: 'DailyHoursResource',
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date'),
        new OA\Property(property: 'type', type: 'string', enum: ['praca', 'l4', 'urlop']),
        new OA\Property(property: 'source', type: 'string', example: 'Praca'),
        new OA\Property(property: 'hours', type: 'number', format: 'float', example: 8),
    ]
)]
class DailyHoursResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->resource->getDate(),
            'type' => $this->resource->getType(),
            'source' => $this->resource->getSource(),
            'hours' => $this->resource->getHours(),
        ];
    }
}
