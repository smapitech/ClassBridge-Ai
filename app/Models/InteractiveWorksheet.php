<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InteractiveWorksheet extends Model {
    protected $fillable=['school_id','teacher_id','class_id','subject_id','title','description','age_group','worksheet_type','instructions','content_json','answer_key','status','due_at'];
    protected function casts():array{return['content_json'=>'array','answer_key'=>'array','due_at'=>'datetime'];}
    public function teacher(){return $this->belongsTo(User::class,'teacher_id');}
    public function classe(){return $this->belongsTo(Classe::class,'class_id');}
    public function subject(){return $this->belongsTo(Subject::class);}
    public function attempts(){return $this->hasMany(WorksheetAttempt::class,'worksheet_id');}
    public function scopeForSchool($q,$id){return $q->where('school_id',$id);}
    public function scopePublished($q){return $q->where('status','published');}
}