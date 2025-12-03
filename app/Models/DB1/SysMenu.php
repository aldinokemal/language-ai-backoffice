<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SysMenu extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'url',
        'icon',
        'parent_id',
        'show_if_has_permission',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysMenu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SysMenu::class, 'parent_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('system_menus')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Menu baru dibuat',
                'updated' => 'Data menu diperbarui',
                'deleted' => 'Menu dihapus',
                default => "Menu {$eventName}"
            });
    }
}
