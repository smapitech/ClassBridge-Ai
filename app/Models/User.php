<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role_id',
        'school_id',
        'avatar',
        'status',
        'metadata',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Phase 2: Role-specific profiles
    public function teacherProfile() { return $this->hasOne(\App\Models\TeacherProfile::class); }
    public function studentProfile() { return $this->hasOne(\App\Models\StudentProfile::class); }
    public function parentProfile() { return $this->hasOne(\App\Models\ParentProfile::class); }

    // Phase 2: Classes the student/teacher is assigned to
    public function classesAsTeacher() { return $this->belongsToMany(\App\Models\Classe::class, 'class_teacher', 'teacher_id', 'class_id'); }
    public function classesAsStudent() { return $this->belongsToMany(\App\Models\Classe::class, 'class_student', 'student_id', 'class_id'); }
    public function children() { return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')->withPivot('relationship')->withTimestamps(); }

    // Phase 4: Coding assignment submissions
    public function submissions() { return $this->hasMany(\App\Models\CodingAssignmentSubmission::class, 'student_id'); }

    /*
    |--------------------------------------------------------------------------
    | Role Checks
    |--------------------------------------------------------------------------
    */

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isSchoolOwner(): bool
    {
        return $this->hasRole('school_owner');
    }

    public function isSchoolAdmin(): bool
    {
        return $this->hasRole('school_admin');
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function recordLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    public function displayName(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        return $this->name;
    }

    public function points(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentPoint::class, 'student_id');
    }

    public function badges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentBadge::class, 'student_id');
    }
}
