<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Homework extends Model
{
    protected $fillable = ['school_id','class_id','subject_id','teacher_id','title','instructions','attachment','due_at','status'];
    protected function casts(): array { return ['due_at'=>'datetime']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class,'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function submissions(): HasMany { return $this->hasMany(HomeworkSubmission::class); }
    public function scopeForSchool($q,$id) { return $q->where('school_id',$id); }
    public function scopePublished($q) { return $q->where('status','published'); }
}