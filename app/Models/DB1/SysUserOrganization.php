<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SysUserOrganization extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'is_default',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(SysOrganization::class);
    }

    public function organizationRoles(): HasMany
    {
        return $this->hasMany(SysUserOrganizationRole::class, 'user_organization_id');
    }
}
