<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    protected $table = 'student_profiles';
    protected $fillable = ['user_id', 'school_id', 'admission_number', 'date_of_birth', 'age', 'gender', 'class_id', 'learning_level', 'special_notes', 'status'];

    protected function casts(): array { return ['date_of_birth' => 'date']; }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function class(): BelongsTo { return $this->belongsTo(Classe::class, 'class_id'); }
    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}