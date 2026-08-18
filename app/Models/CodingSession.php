<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CodingSession extends Model
{
    protected $fillable = [
        'school_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'student_id',
        'coding_assignment_id',
        'title',
        'slug',
        'join_code',
        'status',
        'lesson_mode',
        'active_file_key',
        'permissions',
        'lesson_steps',
        'metadata',
        'started_at',
        'ended_at',
        'last_saved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'lesson_steps' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'last_saved_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function class(): BelongsTo { return $this->belongsTo(Classe::class, 'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function assignment(): BelongsTo { return $this->belongsTo(CodingAssignment::class, 'coding_assignment_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function participants(): HasMany { return $this->hasMany(CodingSessionParticipant::class); }
    public function files(): HasMany { return $this->hasMany(CodingSessionFile::class); }
    public function messages(): HasMany { return $this->hasMany(CodingSessionMessage::class); }
    public function events(): HasMany { return $this->hasMany(CodingSessionEvent::class); }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('is_active', true);
    }

    public function activeFile(): ?CodingSessionFile
    {
        if (! $this->active_file_key) {
            return $this->files()->orderBy('sort_order')->first();
        }

        return $this->files()->where('filename', $this->active_file_key)->first()
            ?? $this->files()->orderBy('sort_order')->first();
    }

    public static function generateJoinCode(): string
    {
        return 'CB-' . Str::upper(Str::random(4) . '-' . Str::random(4));
    }

    public function endSession(): void
    {
        $this->ended_at = now();
        $this->status = 'ended';
        $this->save();

        $this->activeParticipants()->update([
            'is_active' => false,
            'left_at' => now(),
        ]);
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
