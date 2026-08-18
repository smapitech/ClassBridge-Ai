<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonReplay extends Model {
    protected $fillable = ['school_id','classroom_session_id','live_classroom_id','title','summary','replay_data','visibility','status','created_by'];
    protected function casts():array{return['replay_data'=>'array'];}
    public function session():BelongsTo{return $this->belongsTo(ClassroomSession::class,'classroom_session_id');}
    public function classroom():BelongsTo{return $this->belongsTo(LiveClassroom::class,'live_classroom_id');}
    public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}
    public function scopeForSchool($q,$id){return $q->where('school_id',$id);}
    public function scopeVisibleTo($q,$role){return $q->where('visibility',$role)->orWhere('visibility','school_admin');}
}