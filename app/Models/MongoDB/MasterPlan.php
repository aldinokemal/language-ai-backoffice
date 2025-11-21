<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class MasterPlan extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'master_plans';

    protected $fillable = [
        'plan_name',
        'plan_code',
        'price',
        'currency',
        'interval',
        'duration',
        'features',
        'is_active',
        'is_popular',
        'is_displayed',
        'max_usage',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'is_displayed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Get users with this plan
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'plan_id');
    }

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only displayed plans
     */
    public function scopeDisplayed($query)
    {
        return $query->where('is_displayed', true);
    }

    /**
     * Scope to get popular plans
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Get monthly max usage for this plan
     */
    public function getMonthlyMaxUsage(): ?int
    {
        return $this->max_usage['monthly'] ?? null;
    }

    /**
     * Check if plan has unlimited usage
     */
    public function hasUnlimitedUsage(): bool
    {
        return $this->getMonthlyMaxUsage() === null || $this->getMonthlyMaxUsage() === 0;
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPrice(): string
    {
        return $this->currency.' '.number_format((float) $this->price, 2);
    }

    /**
     * Get plan features as a list
     */
    public function getFeaturesList(): array
    {
        return $this->features ?? [];
    }
}
