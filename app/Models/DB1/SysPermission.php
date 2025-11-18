<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SysPermission extends Permission
{
    use LogsActivity;

    public function menu(): BelongsTo
    {
        return $this->belongsTo(SysMenu::class, 'menu_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('system_permissions')
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Permission baru dibuat',
                'updated' => 'Data permission diperbarui',
                'deleted' => 'Permission dihapus',
                default => "Permission {$eventName}"
            });
    }
}
