<?php

namespace Database\Seeders;

use App\Enums\BuiltInRole;
use App\Models\DB1\SysUser;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Modules\Home\Notifications\Greeting;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        setPermissionsTeamId(1);
        $faker = Factory::create();

        // Create Superadmin Role
        $listPermission = Permission::get();
        $role           = Role::create(['name' => BuiltInRole::SUPER_ADMIN->value, 'description' => $faker->sentence(3)]);
        $role->syncPermissions($listPermission);

        $user = SysUser::factory()->create([
            'name'     => 'Aldino Kemal',
            'username' => 'aldinokemal',
            'email'    => 'aldinokemal2104@gmail.com',
        ]);

        // Add User Organization
        $defaultOrg = $user->organizations()->create([
            'organization_id' => 1,
            'is_default'      => true,
        ]);

        // Grant Role to User
        $user->organizations->first()->organizationRoles()->create([
            'role_id'    => 1,
            'is_default' => true,
        ]);

        $user->assignRole($role);

        // $user->notify(new Greeting(
        //     'Welcome to ' . config('app.name'),
        //     'You have been registered as a Developer.',
        //     route('dashboard'),
        //     ['database']

        // ));

        // // create random 50 notifications
        // for ($i = 0; $i < 50; $i++) {
        //     $user->notify(new Greeting(
        //         $faker->sentence(3),
        //         $faker->paragraph(1),
        //         route('dashboard'),
        //         ['database']
        //     ));
        // }
    }
}
