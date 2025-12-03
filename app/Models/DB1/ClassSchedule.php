<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the training class that owns the schedule.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the duration of the schedule in minutes.
     */
    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get the duration of the schedule in hours.
     */
    public function getDurationInHoursAttribute(): float
    {
        return $this->start_time->diffInHours($this->end_time, true);
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time->format('d/m/Y H:i');
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time->format('d/m/Y H:i');
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_in_minutes / 60);
        $minutes = $this->duration_in_minutes % 60;

        if ($hours > 0) {
            return $hours.'j '.($minutes > 0 ? $minutes.'m' : '');
        }

        return $minutes.'m';
    }

    /**
     * Check if the schedule is in the past.
     */
    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    /**
     * Check if the schedule is ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->start_time->isPast() && $this->end_time->isFuture();
    }

    /**
     * Check if the schedule is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }
}
