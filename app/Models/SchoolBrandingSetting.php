<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SchoolBrandingSetting extends Model {
    protected $fillable = ['school_id','logo','favicon','primary_color','secondary_color','accent_color','login_background','portal_theme','email_sender_name','support_email','certificate_signature','settings'];
    protected function casts():array{return['settings'=>'array'];}
    public function school(){return $this->belongsTo(School::class);}
    public static function forSchool($schoolId){return static::firstOrCreate(['school_id'=>$schoolId]);}
}