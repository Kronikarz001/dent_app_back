<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PhotoResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
