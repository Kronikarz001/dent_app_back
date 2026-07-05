<?php

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

/**
 * Summary of AnnouncementResource
 */
#[OA\Schema(
    schema: 'AnnouncementResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'published_at', type: 'string', format: 'date'),
        new OA\Property(property: 'author', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class AnnouncementResource extends BasicResource {}
