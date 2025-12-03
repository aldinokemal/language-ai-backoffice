<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

class SysRole extends Role
{
    use LogsActivity;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(SysOrganization::class, 'organization_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('system_roles')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Role baru dibuat',
                'updated' => 'Data role diperbarui',
                'deleted' => 'Role dihapus',
                default => "Role {$eventName}"
            });
    }
}
