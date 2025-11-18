<?php

namespace Modules\System\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\DB1\SysMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ManageMenuController extends Controller
{
    private string $url = '/system/menus';

    private function defaultParser(): array
    {
        return [
            'url'  => $this->url,
            'view' => 'system::menu',
        ];
    }

    public function index()
    {
        Gate::authorize(Permission::SYSTEM_MENUS_VIEW);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Menu', $this->url),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view("system::menu.index")->with($parser);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize(Permission::SYSTEM_MENUS_UPDATE);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Menu', $this->url),
            new Breadcrumbs('Tambah Menu'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'menu'        => null,
        ]);

        return view("system::menu.upsert")->with($parser);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_MENUS_UPDATE);

        $input = $request->validate([
            'name'                   => 'required|string|max:255',
            'url'                    => 'required|string|max:255',
            'icon'                   => 'nullable|string|max:255',
            'parent_id'              => 'nullable|string',
            'show_if_has_permission' => 'nullable|string|max:255',
            'order'                  => 'required|integer',
            'is_active'              => 'required|boolean',
        ]);

        if (!empty($input['parent_id'])) {
            $input['parent_id'] = customDecrypt($input['parent_id']);
        }

        $menu = SysMenu::create($input);

        // Log menu creation
        activity()
            ->causedBy(Auth::user())
            ->performedOn($menu)
            ->event('created')
            ->withProperties([
                'operation' => 'menu_created',
                'menu_data' => $input,
                'parent_menu' => $input['parent_id'] ? SysMenu::find($input['parent_id'])->name : null,
            ])
            ->inLog('system_menus')
            ->log('Menu baru dibuat');

        return redirect($this->url)
            ->with('message', 'Menu berhasil dibuat');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        Gate::authorize(Permission::SYSTEM_MENUS_UPDATE);

        $id   = customDecrypt($id);
        $menu = SysMenu::findOrFail($id);

        $breadcrumbs = [
            new Breadcrumbs('Sistem', $this->url),
            new Breadcrumbs('Manajemen Menu', $this->url),
            new Breadcrumbs('Edit Menu'),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
            'menu'        => $menu,
        ]);

        return view("system::menu.upsert")->with($parser);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Gate::authorize(Permission::SYSTEM_MENUS_UPDATE);

        $id   = customDecrypt($id);
        $menu = SysMenu::findOrFail($id);

        $input = $request->validate([
            'name'                   => 'required|string|max:255',
            'url'                    => 'nullable|string|max:255',
            'icon'                   => 'nullable|string|max:255',
            'parent_id'              => 'nullable|string',
            'show_if_has_permission' => 'nullable|string|max:255',
            'order'                  => 'required|integer',
            'is_active'              => 'required|boolean',
        ]);

        if (!empty($input['parent_id'])) {
            $input['parent_id'] = customDecrypt($input['parent_id']);
        }

        // Get old data for logging
        $oldData = $menu->getOriginal();
        
        $menu->update($input);

        // Log menu update
        activity()
            ->causedBy(Auth::user())
            ->performedOn($menu)
            ->event('updated')
            ->withProperties([
                'operation' => 'menu_updated',
                'old_data' => $oldData,
                'new_data' => $input,
                'parent_menu' => $input['parent_id'] ? SysMenu::find($input['parent_id'])->name : null,
            ])
            ->inLog('system_menus')
            ->log('Menu diperbarui');

        return redirect($this->url)
            ->with('message', 'Menu berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Gate::authorize(Permission::SYSTEM_MENUS_DELETE);

        $id   = customDecrypt($id);
        $menu = SysMenu::find($id);
        
        if ($menu) {
            // Log menu deletion with menu data before deletion
            activity()
                ->causedBy(Auth::user())
                ->performedOn($menu)
                ->event('deleted')
                ->withProperties([
                    'operation' => 'menu_deleted',
                    'deleted_menu_data' => [
                        'name' => $menu->name,
                        'url' => $menu->url,
                        'icon' => $menu->icon,
                        'parent_name' => $menu->parent ? $menu->parent->name : null,
                        'has_children' => $menu->children()->count() > 0,
                    ],
                ])
                ->inLog('system_menus')
                ->log('Menu dihapus');

            $menu->delete();
        }

        return response()->json(['message' => 'Menu berhasil dihapus']);
    }

    public function ajaxDatagrid(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_MENUS_VIEW);

        $menus = SysMenu::query()
            ->select(['id', 'name as Name', 'url as Url', 'icon as Icon', 'parent_id as parentId', 'is_active as IsActive', 'show_if_has_permission'])
            ->orderBy('order')
            ->get();

        $formattedMenus = $menus->map(function ($menu) {
            return [
                'id'                  => customEncrypt($menu->id), // Use false to get deterministic encryption
                'Name'                => $menu->Name,
                'Url'                 => $menu->Url,
                'Icon'                => $menu->Icon,
                'parentId'            => $menu->parentId ? customEncrypt($menu->parentId, ) : null,
                'IsActive'            => $menu->IsActive,
                'ShowIfHasPermission' => $menu->show_if_has_permission,
            ];
        });

        return response()->json($formattedMenus);
    }

    public function updateStatus(Request $request)
    {
        Gate::authorize(Permission::SYSTEM_MENUS_UPDATE);

        $input = $request->validate([
            'ids'       => 'required|array',
            'is_active' => 'required',
        ]);

        $ids = array_map(function ($id) {
            return customDecrypt($id);
        }, $input['ids']);

        // Get affected menus for logging
        $affectedMenus = SysMenu::whereIn('id', $ids)->get();
        
        SysMenu::whereIn('id', $ids)->update(['is_active' => $input['is_active']]);

        // Log bulk status update
        activity()
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'operation' => 'menu_status_bulk_update',
                'status' => $input['is_active'],
                'affected_menus' => $affectedMenus->map(function ($menu) {
                    return [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'url' => $menu->url,
                    ];
                })->toArray(),
                'total_affected' => count($ids),
            ])
            ->inLog('system_menus')
            ->log('Status menu diperbarui secara massal');

        return response()->json(['message' => 'Status menu berhasil diperbarui']);
    }

    public function ajaxTreeMenu()
    {
        Gate::authorize(Permission::SYSTEM_MENUS_VIEW);

        $menus = SysMenu::query()
            ->select(['id', 'name', 'parent_id'])
            ->orderBy('order')
            ->get();

        $formattedMenus = $menus->map(function ($menu) {
            return [
                'id'          => customEncrypt($menu->id),
                'text'        => $menu->name,
                'parentId'    => $menu->parent_id ? customEncrypt($menu->parent_id) : null,
                'hasChildren' => false,
                'expanded'    => true,
            ];
        });

        $tree = $this->buildTree($formattedMenus);

        return response()->json($tree);
    }

    private function buildTree(Collection $items, $parentId = null)
    {
        $children = $items->where('parentId', $parentId)
            ->map(function ($item) use ($items) {
                $childItems = $this->buildTree($items, $item['id']);
                if (count($childItems) > 0) {
                    $item['hasChildren'] = true;
                    $item['items'] = $childItems;
                }
                return $item;
            })
            ->values()
            ->toArray();

        return $children;
    }

    public function ajaxListPermission()
    {
        Gate::authorize(Permission::SYSTEM_PERMISSIONS_VIEW);

        $permissions = \Spatie\Permission\Models\Permission::query()
            ->select(['id', 'name', 'alias'])
            ->get();

        return response()->json($permissions);
    }
}

