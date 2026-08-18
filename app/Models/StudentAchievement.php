<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model {
    protected $fillable = ['school_id','student_id','achievement_type','title','description','points_awarded','metadata','achieved_at'];
    protected function casts(): array { return ['points_awarded'=>'integer','achieved_at'=>'datetime','metadata'=>'array']; }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function scopeForSchool($q,$id){return $q->where('school_id',$id);}
}