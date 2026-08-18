<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherFeedback extends Model
{
    protected $table = 'teacher_feedback';
    protected $fillable = ['school_id','teacher_id','student_id','class_id','subject_id','feedback_type','title','comment','visibility'];
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class,'class_id'); }
    public function scopeForSchool($q,$id) { return $q->where('school_id',$id); }
    public function scopeVisibleToParents($q) { return $q->where('visibility','parent_visible'); }
}