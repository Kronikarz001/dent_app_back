<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Summary of RoomRequest
 */
class RoomRequest extends FormRequest
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
        /** @var Room|null $room */
        $room = $this->route('room');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room?->uuid, 'uuid')],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa gabinetu jest wymagana.',
            'name.string' => 'Nazwa gabinetu musi być tekstem.',
            'name.max' => 'Nazwa gabinetu może mieć maksymalnie 255 znaków.',
            'name.unique' => 'Gabinet o tej nazwie już istnieje.',
            'description.string' => 'Opis musi być tekstem.',
            'is_active.boolean' => 'Pole is_active musi być wartością logiczną.',
        ];
    }
}
