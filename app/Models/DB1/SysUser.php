<?php

namespace App\Models\DB1;

use Illuminate\Auth\MustVerifyEmail as VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SysUser extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, VerifyEmail, HasRoles, SoftDeletes, LogsActivity;

    protected $table = 'sys_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'name',
        'picture_storage',
        'picture',
        'phone',
        'username',
        'banned_at',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function notifications(): MorphMany
    {
        return $this->morphMany(SysNotification::class, 'notifiable')->latest();
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(SysUserOrganization::class, 'user_id');
    }

    public function fbTokens(): HasMany
    {
        return $this->hasMany(SysUserFbToken::class, 'user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'username', 'phone', 'picture', 'picture_storage', 'banned_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('system_users')
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Pengguna baru dibuat',
                'updated' => 'Data pengguna diperbarui', 
                'deleted' => 'Pengguna dihapus',
                default => "Pengguna {$eventName}"
            });
    }

}
