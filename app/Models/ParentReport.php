<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentReport extends Model
{
    protected $fillable = ['school_id','student_id','class_id','generated_by','report_period_start','report_period_end','attendance_summary','homework_summary','quiz_summary','teacher_comments','ai_summary','status','published_at'];
    protected function casts(): array { return ['report_period_start'=>'datetime','report_period_end'=>'datetime','published_at'=>'datetime','attendance_summary'=>'array','homework_summary'=>'array','quiz_summary'=>'array']; }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class,'class_id'); }
    public function generator(): BelongsTo { return $this->belongsTo(User::class,'generated_by'); }
    public function scopeForSchool($q,$id) { return $q->where('school_id',$id); }
    public function scopePublished($q) { return $q->where('status','published'); }
}