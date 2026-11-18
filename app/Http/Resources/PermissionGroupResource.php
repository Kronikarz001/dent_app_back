<?php

namespace App\Http\Resources;

use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PermissionGroup */
class PermissionGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => PermissionResource::collection($this->permissions),
            'users' => PersonSummaryResource::collection($this->whenLoaded('users')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
