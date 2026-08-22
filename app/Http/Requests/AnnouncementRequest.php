<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Summary of AnnouncementRequest
 */
class AnnouncementRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published_at' => ['required', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tytuł jest wymagany.',
            'title.string' => 'Tytuł musi być tekstem.',
            'title.max' => 'Tytuł może mieć maksymalnie 255 znaków.',
            'content.required' => 'Treść jest wymagana.',
            'content.string' => 'Treść musi być tekstem.',
            'published_at.required' => 'Data publikacji jest wymagana.',
            'published_at.date_format' => 'Data publikacji musi być w formacie RRRR-MM-DD.',
        ];
    }
}
