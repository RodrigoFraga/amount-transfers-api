<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=RolesAndPermissionsSeeder
     * @return void
     */
    public function run ()
    {
        // DEFINE GUARD
        $guard_api = ['guard_name' => 'api'];

        //  ROLES
        $roleUser  = Role::updateOrCreate(['name' => UserRoles::USER], $guard_api);
        $roleStore = Role::updateOrCreate(['name' => UserRoles::STORE], $guard_api);


        $usersPermissions = [];

        $usersPermissions[] = Permission::updateOrCreate(['name' => 'transfer:store'], array_merge($guard_api, ['description' => 'Realizar Transfência']));

        $roleUser->givePermissionTo( $usersPermissions);

    }
}
