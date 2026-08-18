<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OnboardingStep extends Model {
    protected $fillable=['school_id','step_key','title','description','completed_at'];
    protected function casts():array{return['completed_at'=>'datetime'];}
}