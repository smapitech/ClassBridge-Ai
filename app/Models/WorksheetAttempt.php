<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WorksheetAttempt extends Model {
    protected $fillable=['school_id','worksheet_id','student_id','answers_json','score','status','started_at','submitted_at','teacher_feedback'];
    protected function casts():array{return['answers_json'=>'array','score'=>'decimal:2','started_at'=>'datetime','submitted_at'=>'datetime'];}
    public function worksheet(){return $this->belongsTo(InteractiveWorksheet::class,'worksheet_id');}
    public function student(){return $this->belongsTo(User::class,'student_id');}
}