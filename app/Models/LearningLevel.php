<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LearningLevel extends Model {
    protected $fillable = ['school_id','name','slug','description','min_points','max_points','icon','sort_order','status'];
    protected function casts(): array { return ['min_points'=>'integer','max_points'=>'integer']; }
    public function scopeActive($q) { return $q->where('status','active'); }
    public function scopeGlobal($q) { return $q->whereNull('school_id'); }
    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
}