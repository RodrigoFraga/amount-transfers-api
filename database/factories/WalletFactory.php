<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition (): array
    {
        return [
            'personable_type' => $this->faker->name(),
            'personable_id'   => rand(1, 100),
        ];
    }
}
