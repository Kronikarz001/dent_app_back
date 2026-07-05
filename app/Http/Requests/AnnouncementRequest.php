<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Summary of AnnouncementRequest
 */
class AnnouncementRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        $announcementUuid = $this->route('announcement')?->uuid;

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published_at' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('announcements', 'published_at')->ignore($announcementUuid, 'uuid'),
            ],
        ];
    }

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
