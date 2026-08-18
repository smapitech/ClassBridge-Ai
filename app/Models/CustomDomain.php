<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomDomain extends Model {
    protected $fillable = ['school_id','domain','status','verification_token','verified_at','metadata'];
    protected function casts():array{return['verified_at'=>'datetime','metadata'=>'array'];}
    public function school(){return $this->belongsTo(School::class);}
}