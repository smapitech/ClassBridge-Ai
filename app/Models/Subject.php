<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Subject extends Model
{
    protected $fillable = ['school_id', 'course_id', 'name', 'slug', 'description', 'category', 'status'];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
    public function scopeActive($q) { return $q->where('status', 'active'); }

    public static function boot()
    {
        parent::boot();
        static::creating(fn($s) => $s->slug = $s->slug ?: Str::slug($s->name));
    }
}
