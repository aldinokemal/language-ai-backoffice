<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SysOrganization extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'logo_storage',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(SysUser::class, 'sys_user_organizations', 'organization_id', 'user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('system_organizations')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Organisasi baru dibuat',
                'updated' => 'Data organisasi diperbarui',
                'deleted' => 'Organisasi dihapus',
                default => "Organisasi {$eventName}"
            });
    }
}
