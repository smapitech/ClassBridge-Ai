<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Classe extends Model
{
    protected $table = 'classes';
    protected $fillable = ['school_id', 'course_id', 'name', 'slug', 'description', 'age_group', 'level', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_teacher', 'class_id', 'teacher_id')
            ->withPivot('subject_id')->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_student', 'class_id', 'student_id')->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_teacher', 'class_id', 'subject_id');
    }

    public function scopeForSchool($query, $schoolId) { return $query->where('school_id', $schoolId); }
    public function scopeActive($query) { return $query->where('status', 'active'); }

    public static function boot()
    {
        parent::boot();
        static::creating(fn($c) => $c->slug = $c->slug ?: Str::slug($c->name));
    }
}
