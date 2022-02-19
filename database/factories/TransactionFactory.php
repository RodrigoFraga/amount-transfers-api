<?php

namespace Database\Factories;

use App\Enums\ExtractEnum;
use App\Enums\TransactionEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition (): array
    {
        return [
            'amount'          => $this->faker->numberBetween(1, 100000),
            'scheduling_date' => Carbon::now(),
            'description'     => $this->faker->sentence,
            'url_receipt'     => $this->faker->url,
        ];
    }
}
