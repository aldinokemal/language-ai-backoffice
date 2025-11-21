<?php

namespace App\Models\MongoDB;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class ChatUsage extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'chat_usages';

    protected $fillable = [
        'user_id',
        'device_id',
        'subscription_id',
        'date',
        'usage',
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'usage' => 'integer',
    ];

    /**
     * Get the user that owns the chat usage
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the device associated with the chat usage
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'device_id');
    }

    /**
     * Get the subscription associated with the chat usage
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by subscription
     */
    public function scopeBySubscription($query, $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    /**
     * Get total usage for a specific period
     */
    public static function getTotalUsage($userId, $startDate = null, $endDate = null)
    {
        $query = self::where('user_id', $userId);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->sum('usage');
    }
}
