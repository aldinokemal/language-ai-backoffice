<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'class_participant_id',
        'schedule_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the training class that owns the attendance.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the participant that owns the attendance.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(ClassParticipant::class, 'class_participant_id');
    }

    /**
     * Get the schedule associated with this attendance.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    /**
     * Get formatted attendance time.
     */
    public function getFormattedAttendanceTimeAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Get attendance date.
     */
    public function getAttendanceDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y');
    }

    /**
     * Get attendance time.
     */
    public function getAttendanceTimeAttribute(): string
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Check if attendance was recorded today.
     */
    public function isToday(): bool
    {
        return $this->created_at->isToday();
    }

    /**
     * Scope for today's attendances.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for attendances by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    /**
     * Scope for attendances by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope for attendances by participant.
     */
    public function scopeByParticipant($query, $participantId)
    {
        return $query->where('class_participant_id', $participantId);
    }
}
