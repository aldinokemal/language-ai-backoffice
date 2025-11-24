<?php

namespace App\Models\MongoDB;

use MongoDB\BSON\ObjectId;
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
     * Get the user's devices (non-revoked only)
     */
    public function devices(): HasMany
    {
        $relation = $this->hasMany(UserDevice::class, 'user_id', '_id');

        // Ensure the local key is converted to ObjectId for MongoDB queries
        // MongoDB Laravel relationships require ObjectId matching, but _id may be returned as string
        $localKey = $this->getKey();
        if ($localKey && is_string($localKey)) {
            // Clear existing constraints and add with ObjectId
            $relation->getQuery()->getQuery()->wheres = [];
            $relation->where('user_id', new ObjectId($localKey));
        }

        // Filter out revoked devices (only return devices where revoked_at is null)
        $relation->whereNull('revoked_at');

        return $relation;
    }

    /**
     * Get the value of the model's primary key for eager loading relationships
     * Override to ensure ObjectId is returned for MongoDB relationships
     */
    public function getKeyForSelectQuery()
    {
        $key = $this->getKey();
        if ($key && is_string($key)) {
            return new ObjectId($key);
        }
        return $key;
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
