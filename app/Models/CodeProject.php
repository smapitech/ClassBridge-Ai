<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CodeProject extends Model
{
    protected $fillable = [
        'school_id', 'student_id', 'teacher_id', 'class_id', 'subject_id',
        'live_classroom_id', 'title', 'slug', 'description', 'project_type',
        'status', 'visibility', 'created_by',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function files(): HasMany { return $this->hasMany(CodeFile::class); }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }

    public static function boot()
    {
        parent::boot();
        static::creating(fn($p) => $p->slug = $p->slug ?: Str::slug($p->title));
    }
}