<?php

use App\Http\Middleware\CachedAuth;
use App\Http\Middleware\LogoutIfBanned;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\ManageMenuController;
use Modules\System\Http\Controllers\ManageOrganizationController;
use Modules\System\Http\Controllers\ManagePermissionController;
use Modules\System\Http\Controllers\ManageRoleController;
use Modules\System\Http\Controllers\ManageUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('system')
    ->middleware([
        CachedAuth::class,
        EnsureEmailIsVerified::class,
        LogoutIfBanned::class,
    ])
    ->group(function () {
        Route::redirect("/", "/system/users");

        Route::resource("users", ManageUserController::class);
        Route::post('users/ajax/{action}', [ManageUserController::class, 'ajaxBannedOrUnbanned'])
            ->whereIn('action', ['banned', 'unbanned']);
        Route::post('users/ajax/datagrid', [ManageUserController::class, 'ajaxDatagrid'])->name('users.ajax.datagrid');
        Route::post('users/ajax/organization-and-role', [ManageUserController::class, 'ajaxOrganizationAndRole'])->name('users.ajax.organization-and-role');

        Route::resource("menus", ManageMenuController::class)->except('show');
        Route::post('menus/ajax/treegrid', [ManageMenuController::class, 'ajaxDatagrid'])->name('menus.ajax.treegrid');
        Route::post('menus/status', [ManageMenuController::class, 'updateStatus'])->name('menus.status');
        Route::get('menus/ajax/tree-menu', [ManageMenuController::class, 'ajaxTreeMenu'])->name('menus.ajax.tree-menu');
        Route::get('menus/ajax/list-permission', [ManageMenuController::class, 'ajaxListPermission'])->name('menus.ajax.list-permission');

        Route::resource('permissions', ManagePermissionController::class)->except(['show', 'create', 'edit']);
        Route::post('permissions/ajax/datagrid', [ManagePermissionController::class, 'ajaxDatagrid'])->name('permissions.ajax.datagrid');

        Route::resource('organizations', ManageOrganizationController::class);
        Route::post('organizations/ajax/datagrid', [ManageOrganizationController::class, 'ajaxDatagrid'])->name('organizations.ajax.datagrid');

        Route::resource('roles', ManageRoleController::class)->except(['show']);
        Route::post('roles/ajax/datagrid', [ManageRoleController::class, 'ajaxDatagrid'])->name('roles.ajax.datagrid');
        Route::post('roles/ajax/role-permissions', [ManageRoleController::class, 'ajaxRolePermissions'])->name('roles.ajax.role-permissions');
        Route::post('roles/ajax/role-granted-users', [ManageRoleController::class, 'ajaxRoleGrantedUsers'])->name('roles.ajax.role-granted-users');
        Route::put('roles/{role}/update-permissions', [ManageRoleController::class, 'updatePermissions'])->name('roles.update-permissions');
        Route::put('roles/{role}/update-granted-users', [ManageRoleController::class, 'updateGrantedUsers'])
            ->name('roles.update-granted-users');
    });
