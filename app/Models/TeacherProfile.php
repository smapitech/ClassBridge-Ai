<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfile extends Model
{
    protected $table = 'teacher_profiles';
    protected $fillable = ['user_id', 'school_id', 'bio', 'qualification', 'specialization', 'years_of_experience', 'hourly_rate', 'availability', 'status'];

    protected function casts(): array { return ['availability' => 'array', 'hourly_rate' => 'decimal:2']; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}