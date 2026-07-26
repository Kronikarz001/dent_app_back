<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Summary of CompanyResource
 */
class CompanyResource extends BasicResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'regon' => $this->regon,
            'nip' => $this->nip,
            'address' => $this->address,
            'province' => $this->province,
            'district' => $this->district,
            'municipality' => $this->municipality,
            'business_form' => $this->business_form,
            'type_of_business' => $this->type_of_business,
            'form_of_ownership' => $this->form_of_ownership,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
