<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = ['school_id','class_id','live_classroom_id','classroom_session_id','student_id','teacher_id','status','joined_at','left_at','duration_minutes','notes','attendance_date'];
    protected function casts(): array { return ['joined_at'=>'datetime','left_at'=>'datetime','attendance_date'=>'date']; }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class,'class_id'); }
    public function scopeForSchool($q,$id) { return $q->where('school_id',$id); }
}