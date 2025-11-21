<?php

namespace App\Models\MongoDB;

use Carbon\Carbon;
use MongoDB\Laravel\Eloquent\Model;

class AppSumoWebhook extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'appsumo_webhooks';

    protected $fillable = [
        'metadata',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Scope to filter by event type
     */
    public function scopeByEvent($query, $event)
    {
        return $query->where('metadata.event', $event);
    }

    /**
     * Scope to filter by license key
     */
    public function scopeByLicenseKey($query, $licenseKey)
    {
        return $query->where('metadata.license_key', $licenseKey);
    }

    /**
     * Scope to filter by license status
     */
    public function scopeByLicenseStatus($query, $status)
    {
        return $query->where('metadata.license_status', $status);
    }

    /**
     * Get the license key from metadata
     */
    public function getLicenseKey(): ?string
    {
        return $this->metadata['license_key'] ?? null;
    }

    /**
     * Get the event type from metadata
     */
    public function getEvent(): ?string
    {
        return $this->metadata['event'] ?? null;
    }

    /**
     * Get the license status from metadata
     */
    public function getLicenseStatus(): ?string
    {
        return $this->metadata['license_status'] ?? null;
    }

    /**
     * Get the tier from metadata
     */
    public function getTier(): ?int
    {
        return $this->metadata['tier'] ?? null;
    }

    /**
     * Get the event timestamp from metadata
     */
    public function getEventTimestamp(): ?Carbon
    {
        $timestamp = $this->metadata['event_timestamp'] ?? null;

        return $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
    }

    /**
     * Check if this is a test webhook
     */
    public function isTest(): bool
    {
        return $this->metadata['test'] ?? false;
    }

    /**
     * Get the previous license key (for upgrades/downgrades)
     */
    public function getPreviousLicenseKey(): ?string
    {
        return $this->metadata['prev_license_key'] ?? null;
    }

    /**
     * Get the reason from extra metadata (if available)
     */
    public function getReason(): ?string
    {
        return $this->metadata['extra']['reason'] ?? null;
    }

    /**
     * Get formatted metadata for logging/display
     */
    public function getFormattedMetadata(): array
    {
        return [
            'event' => $this->getEvent(),
            'license_key' => $this->getLicenseKey(),
            'license_status' => $this->getLicenseStatus(),
            'tier' => $this->getTier(),
            'event_timestamp' => $this->getEventTimestamp()?->toISOString(),
            'is_test' => $this->isTest(),
            'previous_license_key' => $this->getPreviousLicenseKey(),
            'reason' => $this->getReason(),
        ];
    }
}
