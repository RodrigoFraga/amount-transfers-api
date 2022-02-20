<?php

namespace Database\Factories;

use App\Models\User;
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
        $user = User::factory()->create();

        return [
            'personable_type' => get_class($user),
            'personable_id'   => $user->id
        ];
    }
}
