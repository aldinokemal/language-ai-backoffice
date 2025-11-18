<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysUserOrganizationRole extends Model
{
    protected $fillable = [
        'user_organization_id',
        'role_id',
        'is_default',
    ];

    public function userOrganization(): BelongsTo
    {
        return $this->belongsTo(SysUserOrganization::class, 'user_organization_id');
    }

    public function userRole(): BelongsTo
    {
        return $this->belongsTo(SysRole::class, 'role_id');
    }
}
