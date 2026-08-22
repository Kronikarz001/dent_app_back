<?php

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

/**
 * Summary of MessageGroupResource
 */
#[OA\Schema(
    schema: 'MessageGroupResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'is_default', type: 'boolean'),
        new OA\Property(property: 'creator', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'users', type: 'array', items: new OA\Items(ref: '#/components/schemas/UserResource')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class MessageGroupResource extends BasicResource {}
