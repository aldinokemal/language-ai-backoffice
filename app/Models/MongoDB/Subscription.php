<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class Subscription extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'plan_id',
        'price',
        'currency',
        'interval',
        'duration',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the plan associated with the subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(MasterPlan::class, 'plan_id');
    }

    /**
     * Get the chat usages for this subscription
     */
    public function chatUsages(): HasMany
    {
        return $this->hasMany(ChatUsage::class, 'subscription_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        $now = now();

        return $this->start_date <= $now && ($this->end_date === null || $this->end_date >= $now);
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return ! $this->isActive();
    }

    /**
     * Get subscription duration in days
     */
    public function getDurationInDays(): ?int
    {
        if (! $this->start_date || ! $this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }
}
