<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class User extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'picture',
        'activated_at',
        'plan_id',
        'alert_setting',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the user's current subscription
     */
    public function subscription(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    /**
     * Get the user's devices
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    /**
     * Get the user's current plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(MasterPlan::class, 'plan_id');
    }

    /**
     * Get the user's chat usages
     */
    public function chatUsages(): HasMany
    {
        return $this->hasMany(ChatUsage::class, 'user_id');
    }

    /**
     * Check if user is activated
     */
    public function isActivated(): bool
    {
        return ! is_null($this->activated_at);
    }

    /**
     * Check if user has device login alerts enabled
     */
    public function hasDeviceLoginAlerts(): bool
    {
        return $this->alert_setting['device_login'] ?? false;
    }

    /**
     * Check if user has importance update alerts enabled
     */
    public function hasImportanceUpdateAlerts(): bool
    {
        return $this->alert_setting['importance_update'] ?? false;
    }
}
