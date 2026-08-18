<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomActivityEvent extends Model {
    protected $fillable = ['school_id','classroom_session_id','live_classroom_id','user_id','event_type','title','description','event_data','occurred_at'];
    protected function casts():array { return ['event_data'=>'array','occurred_at'=>'datetime']; }
    public function session():BelongsTo{return $this->belongsTo(ClassroomSession::class,'classroom_session_id');}
    public function user():BelongsTo{return$this->belongsTo(User::class);}
    public function scopeForSession($q,$sid){return $q->where('classroom_session_id',$sid);}
}