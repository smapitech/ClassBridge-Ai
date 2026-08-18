<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    protected $fillable = ['school_id','homework_id','student_id','answer','attachment','submitted_at','status','score','teacher_feedback'];
    protected function casts(): array { return ['submitted_at'=>'datetime','score'=>'decimal:2']; }
    public function homework(): BelongsTo { return $this->belongsTo(Homework::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function scopeForSchool($q,$id) { return $q->where('school_id',$id); }
}