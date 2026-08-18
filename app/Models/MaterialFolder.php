<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MaterialFolder extends Model {
    protected $fillable=['school_id','user_id','parent_id','name','slug','visibility'];
    public function materials(){return $this->hasMany(TeachingMaterial::class,'folder_id');}
    public function children(){return $this->hasMany(self::class,'parent_id');}
    public function scopeForSchool($q,$id){return $q->where('school_id',$id);}
}