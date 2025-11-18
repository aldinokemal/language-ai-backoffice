<?php

namespace Modules\System\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\DB1\SysPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class ManagePermissionController extends Controller
{
    private string $url = '/system/permissions';

    private function defaultParser(): array
    {
        return [
            'url'  => $this->url,
            'view' => 'system::permission',
        ];
    }

    public function index()
    {
        Gate::authorize(Permission::SYSTEM_PERMISSIONS_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Izin', $this->url),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view("system::permission.index")->with($parser);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_PERMISSIONS_CREATE);

        $models = json_decode($request->input('models'), true);
        $model  = $models[0]; // Get first model from array

        $validator = \Validator::make($model, [
            'name'       => 'required|string|max:255',
            'alias'      => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
            'menu_id'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $menuId = customDecrypt($model['menu_id']);
        $permission = SysPermission::create([
            'name'       => $model['name'],
            'alias'      => $model['alias'],
            'guard_name' => $model['guard_name'],
            'menu_id'    => $menuId,
        ]);

        // Log permission creation
        activity()
            ->causedBy(Auth::user())
            ->performedOn($permission)
            ->event('created')
            ->withProperties([
                'operation' => 'permission_created',
                'permission_data' => [
                    'name' => $model['name'],
                    'alias' => $model['alias'],
                    'guard_name' => $model['guard_name'],
                    'menu_name' => $permission->menu->name,
                ],
            ])
            ->inLog('system_permissions')
            ->log('Permission baru dibuat');

        return response()->json([
            'data'    => [
                'id'         => customEncrypt($permission->id),
                'name'       => $permission->name,
                'alias'      => $permission->alias,
                'guard_name' => $permission->guard_name,
                'menu_id'    => $permission->menu_id,
                'menu_name'  => $permission->menu->name,
            ],
            'message' => 'Izin berhasil dibuat',
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_PERMISSIONS_UPDATE);

        $id     = customDecrypt($id);
        $models = json_decode($request->input('models'), true);
        $model  = $models[0]; // Get first model from array

        $validator = \Validator::make($model, [
            'name'       => 'required|string|max:255',
            'alias'      => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
            'menu_id'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $permission = SysPermission::findOrFail($id);
        
        // Get old data for logging
        $oldData = [
            'name' => $permission->name,
            'alias' => $permission->alias,
            'guard_name' => $permission->guard_name,
            'menu_name' => $permission->menu->name,
        ];

        $menuId = customDecrypt($model['menu_id']);
        $permission->update([
            'name'       => $model['name'],
            'alias'      => $model['alias'],
            'guard_name' => $model['guard_name'],
            'menu_id'    => $menuId,
        ]);

        // Log permission update
        activity()
            ->causedBy(Auth::user())
            ->performedOn($permission)
            ->event('updated')
            ->withProperties([
                'operation' => 'permission_updated',
                'old_data' => $oldData,
                'new_data' => [
                    'name' => $model['name'],
                    'alias' => $model['alias'],
                    'guard_name' => $model['guard_name'],
                    'menu_name' => $permission->menu->name,
                ],
            ])
            ->inLog('system_permissions')
            ->log('Permission diperbarui');

        return response()->json([
            'data'    => [
                'id'         => customEncrypt($permission->id),
                'name'       => $permission->name,
                'alias'      => $permission->alias,
                'guard_name' => $permission->guard_name,
                'menu_id'    => customEncrypt($permission->menu_id),
                'menu_name'  => $permission->menu->name,
            ],
            'message' => 'Izin berhasil diperbarui',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Gate::authorize(Permission::SYSTEM_PERMISSIONS_DELETE);
        $id = customDecrypt($id);

        // Get permission data for logging before deletion
        $permission = SysPermission::with('menu')->find($id);
        $permissionData = null;
        
        if ($permission) {
            $permissionData = [
                'name' => $permission->name,
                'alias' => $permission->alias,
                'guard_name' => $permission->guard_name,
                'menu_name' => $permission->menu ? $permission->menu->name : null,
            ];
        }

        try {
            SysPermission::findById($id)->delete();
        } catch (PermissionDoesNotExist $e) {
            DB::table('sys_permissions')->where('id', $id)->delete();
        }

        // Log permission deletion
        if ($permissionData) {
            activity()
                ->causedBy(Auth::user())
                ->event('deleted')
                ->withProperties([
                    'operation' => 'permission_deleted',
                    'deleted_permission_data' => $permissionData,
                ])
                ->inLog('system_permissions')
                ->log('Permission dihapus');
        }

        return response()->json([
            'message' => 'Izin berhasil dihapus',
        ]);
    }

    public function ajaxDatagrid(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_PERMISSIONS_VIEW);

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

        $formatResponse = $data->map(function ($item) {
            return [
                'id'         => customEncrypt($item->id),
                'name'       => $item->name,
                'alias'      => $item->alias,
                'guard_name' => $item->guard_name,
                'menu_id'    => customEncrypt($item->menu_id),
                'menu_name'  => $item->menu->name,
            ];
        });

        return response()->json([
            'data'  => $formatResponse,
            'total' => $total,
        ]);
    }
}
