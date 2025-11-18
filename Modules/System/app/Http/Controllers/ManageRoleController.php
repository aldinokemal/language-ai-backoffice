<?php

namespace Modules\System\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\DB1\SysOrganization;
use App\Models\DB1\SysPermission;
use App\Models\DB1\SysRole;
use App\Models\DB1\SysUser;
use App\Models\DB1\SysUserOrganization;
use App\Models\DB1\SysUserOrganizationRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ManageRoleController extends Controller
{
    private string $url = '/system/roles';

    private function defaultParser(): array
    {
        return [
            'url'  => $this->url,
            'view' => 'system::role',
        ];
    }

    public function index(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Peran', $this->url),
        ];

        $organizationId = $request->input('organization_id') ?? 1;
        $organization   = SysOrganization::findOrFail(
            $organizationId === 1 ? 1 : customDecrypt($organizationId)
        );


        $roles = SysRole::query()->where('organization_id', $organization->id)->get();
        foreach ($roles as $role) {
            $grantedUsers        = SysUserOrganizationRole::query()->where('role_id', $role->id)->count();
            $role->granted_users = $grantedUsers;
            $role->permissions_count = $role->permissions()->count();
        }

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs'  => $breadcrumbs,
            'organization' => $organization,
            'roles'        => $roles,
        ]);

        return view("system::role.index")->with($parser);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_CREATE);
        $organizationId = customDecrypt($request->get('organization_id'));

        $organization = SysOrganization::find($organizationId);
        if (!$organization) {
            return redirect($this->url);
        }

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Peran', $this->url),
            new Breadcrumbs('Buat Peran', $this->url . '/create'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs'  => $breadcrumbs,
            'organization' => $organization,
            'role'         => null,
        ]);

        return view("system::role.upsert")->with($parser);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_CREATE);

        $input = $request->validate([
            'organization_id' => 'required',
            'name'            => 'required|string|max:255',
            'guard_name'      => 'required|string|max:255',
            'description'     => 'nullable|string|max:255',
        ]);

        $input['organization_id'] = customDecrypt($input['organization_id']);

        $organization = SysOrganization::find($input['organization_id']);
        if (!$organization) {
            return responseJSON('Organisasi tidak ditemukan', 404);
        }

        try {
            DB::beginTransaction();
            SysRole::create([
                'name'            => $input['name'],
                'guard_name'      => $input['guard_name'],
                'organization_id' => $organization->id,
                'description'     => $input['description'],
            ]);

            DB::commit();
            return responseJSON('Peran berhasil dibuat');
        } catch (Exception $e) {
            DB::rollBack();
            logError($e);
            return responseJSON('Terjadi kesalahan saat membuat peran', 500);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $id          = customDecrypt($id);
        $role        = SysRole::with('organization')->findOrFail($id);
        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Peran', $this->url),
            new Breadcrumbs('Edit Peran'),
            new Breadcrumbs($role->organization->name),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'role'        => $role,
        ]);

        return view("system::role.upsert")->with($parser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_UPDATE);

        $input = $request->validate([
            "name"        => "required|string|max:255",
            "guard_name"  => "required|string|max:255",
            "description" => "nullable|string|max:255",
        ]);

        $id   = customDecrypt($id);
        $role = SysRole::findOrFail($id);

        $role->update([
            'name'        => $input['name'],
            'guard_name'  => $input['guard_name'],
            'description' => $input['description'],
        ]);

        return responseJSON('Peran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_DELETE);

        $id   = customDecrypt($id);
        $role = SysRole::findOrFail($id);

        // delete role has permission
        $role->permissions()->detach();

        // delete sys_user_organization_roles
        SysUserOrganizationRole::query()->where('role_id', $role->id)->delete();

        $role->delete();

        return responseJSON('Peran berhasil dihapus');
    }

    public function ajaxRolePermissions(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_UPDATE);

        $request->validate([
            'role_id' => 'required',
        ]);

        $roleId            = customDecrypt($request->input('role_id'));
        $role              = SysRole::find($roleId);
        $rolePermissionIds = $role ? $role->permissions()->get()->pluck('id')->toArray() : [];

        $permissions = SysPermission::query()
            ->with('menu')
            ->orderBy('name');

        // Handle filtering
        $filters = $request->input('filter.filters', []);
        foreach ($filters as $filter) {
            if (isset($filter['field']) && isset($filter['value'])) {
                $field = $filter['field'];
                $value = $filter['value'];

                // Only use contains filter
                $permissions->where($field, 'ILIKE', "%{$value}%");
            }
        }

        // Get total count after filtering but before pagination
        $total = $permissions->count();

        // Apply pagination
        $pageSize = $request->input('pageSize', 20);
        $page     = $request->input('page', 1);
        $skip     = ($page - 1) * $pageSize;

        $data = $permissions->skip($skip)
            ->take($pageSize)
            ->get();

        $formatResponse = $data->map(function ($item) use ($rolePermissionIds) {
            return [
                'id'         => customEncrypt($item->id),
                'name'       => $item->name,
                'alias'      => $item->alias,
                'guard_name' => $item->guard_name,
                'menu_name'  => $item->menu->name,
                'selected'   => in_array($item->id, $rolePermissionIds),
            ];
        });

        return response()->json([
            'data'  => $formatResponse,
            'total' => $total,
        ]);
    }

    public function ajaxRoleGrantedUsers(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_UPDATE);

        $request->validate([
            'role_id' => 'required',
        ]);

        $roleId         = customDecrypt($request->input('role_id'));
        $role           = SysRole::find($roleId);
        $organizationId = $role->organization_id;

        $query = SysUser::query()
            ->select([
                'sys_users.*',
                DB::raw('CASE WHEN sys_user_organization_roles.id IS NOT NULL THEN 1 ELSE 0 END as "selected"'),
            ])
            ->leftJoin('sys_user_organizations', function ($join) use ($organizationId) {
                $join->on('sys_users.id', '=', 'sys_user_organizations.user_id')
                    ->where('sys_user_organizations.organization_id', $organizationId);
            })
            ->leftJoin('sys_user_organization_roles', function ($join) use ($roleId) {
                $join->on('sys_user_organizations.id', '=', 'sys_user_organization_roles.user_organization_id')
                    ->where('sys_user_organization_roles.role_id', '=', $roleId);
            })
            ->orderByRaw('selected desc')
            ->with('organizations.organizationRoles');

        // Apply filters
        if ($request->has('filter.filters')) {
            $filter = $request->input('filter.filters');
            foreach ($filter as $filterItem) {
                if (!isset($filterItem['field']) || !isset($filterItem['value'])) {
                    continue;
                }

                switch ($filterItem['field']) {
                    case 'name':
                        $query->where('sys_users.name', 'ilike', '%' . strtolower($filterItem['value']) . '%');
                        break;
                    case 'email':
                        $query->where('sys_users.email', 'ilike', '%' . strtolower($filterItem['value']) . '%');
                        break;
                }
            }
        }

        $totalCount = $query->count();
        $users      = $query->skip($request->input('skip'))->take($request->input('take'))->get();

        $formattedData = [
            'total' => $totalCount,
            'data'  => $users->map(function ($user) use ($roleId) {
                return [
                    'id'       => customEncrypt($user->id),
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'selected' => $user->selected,
                ];
            }),
        ];

        return response()->json($formattedData);
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_UPDATE);

        $id   = customDecrypt($id);
        $role = SysRole::findOrFail($id);

        try {
            DB::beginTransaction();

            $permissionIds = collect($request->permission_ids)->map(function ($id) {
                return customDecrypt($id);
            })->toArray();

            $oldPermissions = $role->permissions()->get()->pluck('name', 'id')->toArray();
            $listPermissionIds = SysPermission::query()->whereIn('id', $permissionIds)->pluck('id')->toArray();
            $newPermissions = SysPermission::query()->whereIn('id', $listPermissionIds)->pluck('name', 'id')->toArray();

            $role->syncPermissions($listPermissionIds);

            // Log permission update
            activity()
                ->causedBy(Auth::user())
                ->performedOn($role)
                ->event('updated')
                ->withProperties([
                    'operation' => 'permissions_updated',
                    'role_name' => $role->name,
                    'organization_name' => $role->organization->name,
                    'old_permissions' => $oldPermissions,
                    'new_permissions' => $newPermissions,
                    'added_permissions' => array_diff($newPermissions, $oldPermissions),
                    'removed_permissions' => array_diff($oldPermissions, $newPermissions),
                ])
                ->inLog('system_roles')
                ->log('Permission role diperbarui');

            DB::commit();
            return responseJSON('Izin peran berhasil diperbarui');
        } catch (Exception $e) {
            DB::rollBack();
            logError($e);
            return responseJSON($e->getMessage(), 500);
        }
    }

    public function updateGrantedUsers(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_ROLES_UPDATE);

        $id   = customDecrypt($id);
        $role = SysRole::findOrFail($id);

        try {
            DB::beginTransaction();

            $userIds = collect($request->user_ids)->map(function ($id) {
                return customDecrypt($id);
            })->toArray();

            // Get existing user-organization-role records
            $existingRecords = SysUserOrganizationRole::with('userOrganization')->where('role_id', $role->id)->get();

            // Track changes for logging
            $removedUserIds = [];
            $existingUserIds = $existingRecords->pluck('userOrganization.user_id')->toArray();

            // Remove users that are no longer selected
            $existingRecords->each(function ($record) use ($userIds, &$removedUserIds) {
                if (!in_array($record->userOrganization->user_id, $userIds)) {
                    $removedUserIds[] = $record->userOrganization->user_id;
                    $record->delete();
                }
            });

            // Add new users
            $newUserIds = array_diff($userIds, $existingUserIds);

            foreach ($newUserIds as $userId) {
                // First get or create user organization record
                $userOrg = SysUserOrganization::firstOrCreate([
                    'user_id'         => $userId,
                    'organization_id' => $role->organization_id,
                ], [
                    'is_default' => true,
                ]);


                // Then create user organization role with the user_organization_id
                SysUserOrganizationRole::updateOrCreate([
                    'user_organization_id' => $userOrg->id,
                    'role_id'              => $role->id,
                ], ['is_default' => true]);

                // set false is_default another Org Role if exist
                SysUserOrganizationRole::where('user_organization_id', $userOrg->id)
                    ->where('role_id', '!=', $role->id)
                    ->update(['is_default' => false]);
            }

            // Log user-role assignment changes
            if (!empty($removedUserIds) || !empty($newUserIds)) {
                $removedUsers = SysUser::whereIn('id', $removedUserIds)->pluck('name', 'id')->toArray();
                $addedUsers = SysUser::whereIn('id', $newUserIds)->pluck('name', 'id')->toArray();

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($role)
                    ->event('updated')
                    ->withProperties([
                        'operation' => 'granted_users_updated',
                        'role_name' => $role->name,
                        'organization_name' => $role->organization->name,
                        'added_users' => $addedUsers,
                        'removed_users' => $removedUsers,
                        'total_users' => count($userIds),
                    ])
                    ->inLog('system_roles')
                    ->log('Pengguna role diperbarui');
            }

            DB::commit();
            return responseJSON('Pengguna peran berhasil diperbarui');
        } catch (Exception $e) {
            DB::rollBack();
            logError($e);
            return responseJSON($e->getMessage(), 500);
        }
    }
}
