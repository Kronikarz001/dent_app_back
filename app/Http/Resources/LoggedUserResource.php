<?php

namespace App\Http\Resources;

class LoggedUserResource extends UserResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        unset($data['background_path']);

        return $data;
    }
}
