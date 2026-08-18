<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TeachingMaterial extends Model {
    protected $fillable=['school_id','course_id','user_id','folder_id','title','slug','description','material_type','content','file_path','external_url','metadata','visibility','status'];
    protected function casts():array{return['metadata'=>'array'];}
    public function course(){return $this->belongsTo(Course::class);}
    public function folder(){return $this->belongsTo(MaterialFolder::class,'folder_id');}
    public function user(){return $this->belongsTo(User::class);}
    public function scopeForSchool($q,$id){return $q->where('school_id',$id);}
    public function scopeVisibleTo($q,$visibility){return $q->whereIn('visibility',[$visibility,'school']);}
}
