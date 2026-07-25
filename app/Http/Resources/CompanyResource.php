<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Company */
class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
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
