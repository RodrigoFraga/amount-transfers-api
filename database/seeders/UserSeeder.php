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
    public function run()
    {
        $user = User::updateOrcreate([
            'name'     => 'Usuário',
            'email'    => strtolower('user@user.io'),
            'document'    => '76401429038',
        ], [
            'password' => bcrypt(123123123)
        ]);

        $store = User::updateOrcreate([
            'name'     => 'Store',
            'email'    => strtolower('store@store.io'),
            'document'    => '12914027000179',
        ], [
            'password' => bcrypt(123123123)
        ]);


        $user->assignRole(UserRoles::USER);
        $store->assignRole(UserRoles::STORE);
    }
}
