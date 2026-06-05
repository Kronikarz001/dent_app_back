<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FileResource extends BasicResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
