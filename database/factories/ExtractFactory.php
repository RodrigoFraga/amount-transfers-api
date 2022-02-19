<?php

namespace Database\Factories;

use App\Enums\ExtractEnum;
use App\Models\Extract;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExtractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Extract::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition (): array
    {
        return [
            'value'         => $this->faker->numberBetween(1, 100000),
            'current_value' => $this->faker->numberBetween(1, 100000),
            "type"            => $this->faker->randomElement([ExtractEnum::INCOMING, ExtractEnum::OUTCOMING]),
            'description'   => $this->faker->sentence,
        ];
    }
}
