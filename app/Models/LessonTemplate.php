<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LessonTemplate extends Model {
    protected $table='lesson_templates';
    protected $fillable=['school_id','user_id','title','slug','subject','topic','age_group','level','template_content','metadata','visibility','status'];
    protected function casts():array{return['metadata'=>'array'];}
    public function scopeActive($q){return $q->where('status','active');}
    public function scopeGlobal($q){return $q->whereNull('school_id');}
    public function scopeForSchool($q,$id){return $q->where('school_id',$id)->orWhereNull('school_id');}
}