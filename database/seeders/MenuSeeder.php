<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\DB1\SysMenu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as SpatiePermission;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'name'                   => 'Language AI',
                'is_active'              => true,
                'order'                  => 1,
                'icon'                   => 'ki-artificial-intelligence',
                'permissions'            => [],
                'show_if_has_permission' => null,
                'child'                  => [
                    [
                        'name'                   => 'Dashboard',
                        'is_active'              => true,
                        'order'                  => 0,
                        'icon'                   => null,
                        'permissions'            => [
                            ['name' => Permission::LANGUAGE_AI_DASHBOARD_VIEW, 'alias' => 'VIEW'],
                        ],
                        'show_if_has_permission' => Permission::LANGUAGE_AI_DASHBOARD_VIEW,
                        'url'                    => '/language-ai/dashboard',
                    ],
                    [
                        'name'                   => 'Users',
                        'is_active'              => true,
                        'order'                  => 1,
                        'icon'                   => null,
                        'permissions'            => [
                            ['name' => Permission::LANGUAGE_AI_USERS_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::LANGUAGE_AI_USERS_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::LANGUAGE_AI_USERS_UPDATE, 'alias' => 'UPDATE'],
                        ],
                        'show_if_has_permission' => Permission::LANGUAGE_AI_USERS_VIEW,
                        'url'                    => '/language-ai/users',
                    ],
                    [
                        'name'                   => 'Plans',
                        'is_active'              => true,
                        'order'                  => 2,
                        'icon'                   => null,
                        'permissions'            => [
                            ['name' => Permission::LANGUAGE_AI_PLANS_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::LANGUAGE_AI_PLANS_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::LANGUAGE_AI_PLANS_UPDATE, 'alias' => 'UPDATE'],
                            ['name' => Permission::LANGUAGE_AI_PLANS_DELETE, 'alias' => 'DELETE'],
                        ],
                        'show_if_has_permission' => Permission::LANGUAGE_AI_PLANS_VIEW,
                        'url'                    => '/language-ai/plans',
                    ],
                ],
            ],
            [
                'name'                   => 'Sistem',
                'is_active'              => true,
                'order'                  => 99,
                'icon'                   => 'ki-setting-2',
                'permissions'            => [],
                'show_if_has_permission' => null,
                'child'                  => [
                    [
                        'name'                   => 'Kelola Pengguna',
                        'is_active'              => true,
                        'order'                  => 10,
                        'icon'                   => null,
                        'show_if_has_permission' => Permission::SYSTEM_USERS_VIEW,
                        'url'                    => '/system/users',
                        'permissions'            => [
                            ['name' => Permission::SYSTEM_USERS_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::SYSTEM_USERS_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::SYSTEM_USERS_UPDATE, 'alias' => 'UPDATE'],
                            ['name' => Permission::SYSTEM_USERS_DELETE, 'alias' => 'DELETE'],
                        ],
                    ],
                    [
                        'name'                   => 'Kelola Peran',
                        'is_active'              => true,
                        'order'                  => 20,
                        'icon'                   => null,
                        'show_if_has_permission' => Permission::SYSTEM_ROLES_VIEW,
                        'url'                    => '/system/roles',
                        'permissions'            => [
                            ['name' => Permission::SYSTEM_ROLES_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::SYSTEM_ROLES_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::SYSTEM_ROLES_UPDATE, 'alias' => 'UPDATE'],
                            ['name' => Permission::SYSTEM_ROLES_DELETE, 'alias' => 'DELETE'],
                        ],
                    ],
                    [
                        'name'                   => 'Kelola Izin',
                        'is_active'              => true,
                        'order'                  => 30,
                        'icon'                   => null,
                        'show_if_has_permission' => Permission::SYSTEM_PERMISSIONS_VIEW,
                        'url'                    => '/system/permissions',
                        'permissions'            => [
                            ['name' => Permission::SYSTEM_PERMISSIONS_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::SYSTEM_PERMISSIONS_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::SYSTEM_PERMISSIONS_UPDATE, 'alias' => 'UPDATE'],
                            ['name' => Permission::SYSTEM_PERMISSIONS_DELETE, 'alias' => 'DELETE'],
                        ],
                    ],
                    [
                        'name'                   => 'Kelola Organisasi',
                        'is_active'              => true,
                        'order'                  => 30,
                        'icon'                   => null,
                        'show_if_has_permission' => Permission::SYSTEM_ORGANIZATIONS_VIEW,
                        'url'                    => '/system/organizations',
                        'permissions'            => [
                            ['name' => Permission::SYSTEM_ORGANIZATIONS_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::SYSTEM_ORGANIZATIONS_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::SYSTEM_ORGANIZATIONS_UPDATE, 'alias' => 'UPDATE'],
                            ['name' => Permission::SYSTEM_ORGANIZATIONS_DELETE, 'alias' => 'DELETE'],
                        ],
                    ],
                    [
                        'name'                   => 'Kelola Menu',
                        'is_active'              => true,
                        'order'                  => 40,
                        'icon'                   => null,
                        'show_if_has_permission' => Permission::SYSTEM_MENUS_VIEW,
                        'url'                    => '/system/menus',
                        'permissions'            => [
                            ['name' => Permission::SYSTEM_MENUS_VIEW, 'alias' => 'VIEW'],
                            ['name' => Permission::SYSTEM_MENUS_CREATE, 'alias' => 'CREATE'],
                            ['name' => Permission::SYSTEM_MENUS_UPDATE, 'alias' => 'UPDATE'],
                            ['name' => Permission::SYSTEM_MENUS_DELETE, 'alias' => 'DELETE'],
                        ],
                    ],
                ],
            ],
        ];


        foreach ($menus as $menu) {
            $this->createMenu($menu);
        }
    }

    private function createMenu(array $menu, ?SysMenu $parent = null): void
    {
        $permissions = $menu['permissions'] ?? [];
        unset($menu['permissions']);

        $children = $menu['child'] ?? [];
        unset($menu['child']);

        $menu['parent_id'] = $parent?->id;
        $menuModel         = SysMenu::create($menu);

        if (count($permissions) > 0) {
            foreach ($permissions as $permission) {
                $permission['menu_id'] = $menuModel->id;
                SpatiePermission::firstOrCreate($permission);
            }
        }

        foreach ($children as $child) {
            $this->createMenu($child, $menuModel);
        }
    }
}
