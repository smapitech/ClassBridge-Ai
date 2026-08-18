<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model {
    protected $fillable = ['school_id','name','slug','template_type','background_image','layout_json','status'];
    protected function casts():array{return['layout_json'=>'array'];}
    public function scopeActive($q){return $q->where('status','active');}
    public function scopeGlobal($q){return $q->whereNull('school_id');}
    public function scopeForSchool($q,$id){return $q->where('school_id',$id);}
}