<?php

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

/**
 * Summary of RoomResource
 */
#[OA\Schema(
    schema: 'RoomResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', example: 'Gabinet 1'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class RoomResource extends BasicResource {}
