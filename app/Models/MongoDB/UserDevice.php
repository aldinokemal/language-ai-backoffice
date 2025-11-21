<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class UserDevice extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'user_devices';

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'device_token',
        'device_type',
        'app_version',
        'revoked_at',
    ];

    protected $casts = [
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the device
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get chat usages for this device
     */
    public function chatUsages(): HasMany
    {
        return $this->hasMany(ChatUsage::class, 'device_id');
    }

    /**
     * Scope to get only active (non-revoked) devices
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope to get only revoked devices
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope to filter by device type
     */
    public function scopeByType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Check if device is active
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }

    /**
     * Check if device is revoked
     */
    public function isRevoked(): bool
    {
        return ! $this->isActive();
    }

    /**
     * Revoke the device
     */
    public function revoke(): bool
    {
        return $this->update(['revoked_at' => now()]);
    }

    /**
     * Get device info as array
     */
    public function getDeviceInfo(): array
    {
        return [
            'device_id' => $this->device_id,
            'device_name' => $this->device_name,
            'device_type' => $this->device_type,
            'app_version' => $this->app_version,
            'is_active' => $this->isActive(),
        ];
    }
}
