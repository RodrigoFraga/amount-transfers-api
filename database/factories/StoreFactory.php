<?php

namespace Database\Factories;

use App\Models\User;
use Faker\Provider\pt_BR\Company;
use Faker\Provider\pt_BR\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition (): array
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Company($this->faker));

        return [
            'name'       => $this->faker->name(),
            'legal_name' => $this->faker->name(),
            'email'      => $this->faker->unique()->safeEmail(),
            'document'   => rand() % 2 == 0 ? $this->faker->cnpj(false) : $this->faker->cpf(false),
            'user_id'    => User::factory()->create()->id,
        ];
    }
}
