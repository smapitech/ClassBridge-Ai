<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = ['school_id','class_id','subject_id','teacher_id','title','description','duration_minutes','total_marks','status'];
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class,'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function questions(): HasMany { return $this->hasMany(QuizQuestion::class)->orderBy('sort_order'); }
    public function attempts(): HasMany { return $this->hasMany(QuizAttempt::class); }
    public function scopeForSchool($q,$id) { return $q->where('school_id',$id); }
    public function scopePublished($q) { return $q->where('status','published'); }
}