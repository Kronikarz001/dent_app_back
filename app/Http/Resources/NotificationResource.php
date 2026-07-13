<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Summary of NotificationResource
 */
#[OA\Schema(
    schema: 'NotificationResource',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'message', type: 'string', nullable: true),
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'read', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class NotificationResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? null,
            'url' => $this->data['url'] ?? null,
            'type' => $this->data['type'] ?? null,
            'read' => ! is_null($this->read_at),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
