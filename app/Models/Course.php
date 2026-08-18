<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'slug',
        'description',
        'status',
        'created_by',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Classe::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_learners', 'course_id', 'learner_id')
            ->withPivot(['school_id', 'created_by'])
            ->withTimestamps();
    }

    public function liveClassrooms(): HasMany
    {
        return $this->hasMany(LiveClassroom::class);
    }

    public function teachingMaterials(): HasMany
    {
        return $this->hasMany(TeachingMaterial::class);
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function nextSession(): ?LiveClassroom
    {
        return $this->liveClassrooms()
            ->whereIn('status', ['live', 'scheduled'])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderByRaw('COALESCE(starts_at, scheduled_at, created_at)')
            ->first();
    }

    public function previousSessions()
    {
        return $this->liveClassrooms()
            ->whereIn('status', ['ended', 'archived'])
            ->latest('starts_at')
            ->latest('scheduled_at');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $course) {
            $course->slug = $course->slug ?: Str::slug($course->name);
        });
    }
}
