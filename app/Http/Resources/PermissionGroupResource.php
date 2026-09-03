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
            'permissions' => PermissionResource::collection($this->permissions),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
