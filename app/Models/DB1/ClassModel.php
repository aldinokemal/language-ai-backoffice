<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'class';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the schedules for the training class.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    /**
     * Get the participants for the training class.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ClassParticipant::class, 'class_id');
    }

    /**
     * Get the attendances for the training class.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(ClassAttendance::class, 'class_id');
    }

    /**
     * Scope for active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive classes.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the participant count attribute.
     */
    public function getParticipantCountAttribute(): int
    {
        return $this->participants()->count();
    }

    /**
     * Get the schedule count attribute.
     */
    public function getScheduleCountAttribute(): int
    {
        return $this->schedules()->count();
    }

    /**
     * Check if the class is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the class is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }
}
