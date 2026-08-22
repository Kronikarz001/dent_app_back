<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Summary of CompanyFactor
 */
class CompanyFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Company::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'regon' => $this->faker->word(),
            'nip' => $this->faker->word(),
            'address' => $this->faker->address(),
            'province' => $this->faker->word(),
            'district' => $this->faker->word(),
            'municipality' => $this->faker->word(),
            'business_form' => $this->faker->word(),
            'type_of_business' => $this->faker->word(),
            'form_of_ownership' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
