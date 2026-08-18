<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassroomSession extends Model
{
    protected $fillable = [
        'school_id', 'live_classroom_id', 'teacher_id',
        'started_at', 'ended_at', 'duration_minutes',
        'status', 'whiteboard_snapshot', 'textpad_snapshot', 'active_mode', 'mode_settings', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'whiteboard_snapshot' => 'array',
            'mode_settings' => 'array',
            'metadata' => 'array',
        ];
    }

    public function classroom(): BelongsTo { return $this->belongsTo(LiveClassroom::class, 'live_classroom_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }

    public function participants(): HasMany { return $this->hasMany(ClassroomParticipant::class); }
    public function messages(): HasMany { return $this->hasMany(ClassroomMessage::class); }
    public function whiteboardElements(): HasMany { return $this->hasMany(WhiteboardElement::class); }
    public function whiteboard(): HasOne { return $this->hasOne(Whiteboard::class, 'classroom_session_id'); }

    public function activeParticipants()
    {
        return $this->participants()->where('is_active', true);
    }

    public function endSession(): void
    {
        $this->ended_at = now();
        $this->status = 'ended';
        if ($this->started_at) {
            $this->duration_minutes = (int) $this->started_at->diffInMinutes(now());
        }
        $this->save();

        // Mark all participants as left
        $this->participants()->where('is_active', true)->update([
            'is_active' => false, 'left_at' => now(),
        ]);
    }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
}
