<?php

namespace App\Models\DB1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'name',
        'email',
        'phone',
        'company',
        'position',
        'signature',
        'face_recognition_data_path',
        'certificate_path',
    ];

    protected $casts = [
        'face_recognition_data_path' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the training class that owns the participant.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the attendances for the participant.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(ClassAttendance::class, 'class_participant_id');
    }

    /**
     * Get the participant's full name (if available) or email.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->email ?? 'Unknown Participant');
    }

    /**
     * Get formatted participant information.
     */
    public function getFormattedInfoAttribute(): string
    {
        $info = [];

        if ($this->email) {
            $info[] = $this->email;
        }

        if ($this->company) {
            $info[] = $this->company;
        }

        if ($this->position) {
            $info[] = $this->position;
        }

        return implode(' - ', $info);
    }

    /**
     * Check if participant has signature.
     */
    public function hasSignature(): bool
    {
        return !empty($this->signature);
    }

    /**
     * Check if participant has face recognition data.
     */
    public function hasFaceRecognitionData(): bool
    {
        return !empty($this->face_recognition_data_path);
    }

    /**
     * Get the signature URL.
     */
    public function getSignatureUrlAttribute(): ?string
    {
        // Signature is stored inline (binary/longtext). No URL available.
        return null;
    }

    /**
     * Check if participant has attended the class.
     */
    public function hasAttended(): bool
    {
        return $this->attendances()->exists();
    }

    /**
     * Get attendance count for this participant.
     */
    public function getAttendanceCountAttribute(): int
    {
        return $this->attendances()->count();
    }
}
