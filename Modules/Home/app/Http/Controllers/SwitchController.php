<?php

namespace Modules\Home\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DB1\SysUserOrganization;
use App\Models\DB1\SysUserOrganizationRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Traits\SessionTrait;

class SwitchController extends Controller
{
    use SessionTrait;

    public function switchOrganization(Request $request)
    {
        $defaultOrganization = SysUserOrganization::query()
            ->where('user_id', Auth::id())
            ->where('id', decryptOrAbort($request->user_organization_id))
            ->first();

        $defaultRole = $defaultOrganization->organizationRoles()->where('is_default', true)->first();

        // update default organization in database
        $defaultOrganization->is_default = true;
        $defaultOrganization->save();

        // set another to false
        SysUserOrganization::query()
            ->where('user_id', Auth::id())
            ->where('id', '!=', $defaultOrganization->id)
            ->update(['is_default' => false]);

        $this->setSession($defaultOrganization, $defaultRole);

        return redirect()->route('dashboard');
    }

    public function switchRole(Request $request)
    {
        $defaultOrganization = session('org');
        $defaultRole = $defaultOrganization->organizationRoles()->where('role_id', decryptOrAbort($request->role_id))->first();

        // update default role in database
        $defaultRole->is_default = true;
        $defaultRole->save();

        // set another to false
        SysUserOrganizationRole::query()
            ->where('user_organization_id', $defaultOrganization->id)
            ->where('role_id', '!=', $defaultRole->role_id)
            ->update(['is_default' => false]);

        $this->setSession($defaultOrganization, $defaultRole);

        return redirect()->route('dashboard');
    }
}
