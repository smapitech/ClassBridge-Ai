<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LiveClassroom extends Model
{
    protected $fillable = [
        'school_id', 'course_id', 'class_id', 'subject_id', 'teacher_id',
        'title', 'slug', 'description', 'room_code',
        'status', 'scheduled_at', 'starts_at', 'ends_at',
        'duration_minutes', 'settings', 'classroom_mode', 'layout_settings', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'array',
            'layout_settings' => 'array',
        ];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class, 'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function sessions(): HasMany { return $this->hasMany(ClassroomSession::class); }
    public function activeSession(): ?ClassroomSession
    {
        return $this->sessions()->where('status', 'live')->latest()->first()
            ?? $this->sessions()->where('status', 'waiting')->latest()->first();
    }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }

    public function joinUrl(): string
    {
        return route('join.room', ['roomCode' => $this->room_code]);
    }

    public function initialMode(): string
    {
        return $this->classroom_mode ?: 'whiteboard';
    }

    public static function generateRoomCode(): string
    {
        return 'CLASS-' . strtoupper(Str::random(4)) . '-' . random_int(1000, 9999);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($c) {
            $c->slug = $c->slug ?: Str::slug($c->title);
            $c->room_code = $c->room_code ?: static::generateRoomCode();
            $c->classroom_mode = $c->classroom_mode ?: 'whiteboard';
        });
    }
}
