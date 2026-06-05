<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'extension' => $this->faker->fileExtension(),
            'filename' => $this->faker->name(),
            'size' => $this->faker->randomNumber(),
            'path' => $this->faker->imageUrl(),
            'mimetype' => $this->faker->mimeType(),
            'fileable_id' => $this->faker->uuid(),
            'fileable_type' => $this->faker->word(),
            'user_uuid' => User::factory(),
        ];
    }
}
