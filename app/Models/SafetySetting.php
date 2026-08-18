<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SafetySetting extends Model {
    protected $fillable=['school_id','allow_student_chat','allow_student_drawing','allow_private_teacher_student_chat','require_parent_visibility','record_classroom_activity','show_safety_notice','settings'];
    protected function casts():array{return['settings'=>'array','allow_student_chat'=>'boolean','allow_student_drawing'=>'boolean','allow_private_teacher_student_chat'=>'boolean','require_parent_visibility'=>'boolean','record_classroom_activity'=>'boolean','show_safety_notice'=>'boolean'];}
    public static function forSchool(int $schoolId):self{return static::firstOrCreate(['school_id'=>$schoolId]);}
}