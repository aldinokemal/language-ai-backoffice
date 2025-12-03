<?php

namespace Modules\Auth\Traits;

use App\Models\DB1\SysRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SessionTrait
{
    public function setSession($defaultOrganization = null, $defaultRole = null): void
    {
        $user = Auth::user();
        if (! $defaultOrganization) {
            $defaultOrganization = $user->organizations()->where('is_default', true)->first();
        }
        session(['org' => $defaultOrganization]);

        if (! $defaultRole) {
            $defaultRole = $defaultOrganization->organizationRoles()->where('is_default', true)->first();
        }
        session(['role' => $defaultRole]);

        setPermissionsTeamId($defaultOrganization->organization_id);

        $role = SysRole::findById($defaultRole->role_id);
        $user->syncRoles($role);

        $userRole = $user->roles->first();
        if (! $userRole) {
            return; // No role assigned, skip menu setup
        }

        $listPermission = $userRole->permissions->pluck('name')->toArray();

        // Subquery to get the parent ID
        $parentIds = DB::table('sys_menus')
            ->whereIn('show_if_has_permission', $listPermission)
            ->where('is_active', true)
            ->pluck('parent_id');
        $roleQuery = DB::table('sys_menus')
            ->where('is_active', true)
            ->whereIn('show_if_has_permission', $listPermission);

        $parentQuery = DB::table('sys_menus')
            ->where('is_active', true)
            ->whereIn('id', $parentIds);
        $results = $roleQuery->union($parentQuery)->get();

        $tree = $this->buildTree(collect($results));
        session(['menu' => $tree]);
    }

    public function buildTree(Collection $items, $parentId = null)
    {
        return $items->where('parent_id', $parentId)
            ->sortBy('order') // Sort items by the `order` property
            ->map(function ($item) use ($items) {
                $item->children = $this->buildTree($items, $item->id);

                return $item;
            })->values()->toArray();
    }
}
