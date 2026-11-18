<?php

namespace App\Http\Resources;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Calendar */
class CalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'no_show' => $this->no_show,
            'is_active' => $this->is_active,
            'users' => PersonSummaryResource::collection($this->whenLoaded('users')),
            'patients' => PersonSummaryResource::collection($this->whenLoaded('patients')),
            'dental_examinations' => DentalExaminationResource::collection($this->whenLoaded('dentalExaminations')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
