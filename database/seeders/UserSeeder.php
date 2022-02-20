<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run ()
    {
        $user = User::updateOrcreate([
            'name'     => 'Usuário',
            'email'    => strtolower('user@user.io'),
            'document' => '76401429038',
        ], [
            'password' => bcrypt(123123123)
        ]);

        $user->wallet()->create(['available_balance' => 1000]);

        $user2 = User::updateOrcreate([
            'name'     => 'Usuário 2',
            'email'    => strtolower('user2@user.io'),
            'document' => '76401429040',
        ], [
            'password' => bcrypt(123123123)
        ]);
        $user2->wallet()->create();


        $store = User::updateOrcreate([
            'name'     => 'Store',
            'email'    => strtolower('store@store.io'),
            'document' => '12914027000179',
        ], [
            'password' => bcrypt(123123123)
        ]);

        $store->wallet()->create();


        $user->assignRole(UserRoles::USER);
        $user2->assignRole(UserRoles::USER);
        $store->assignRole(UserRoles::STORE);
    }
}
