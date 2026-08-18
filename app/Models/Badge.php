<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model {
    protected $fillable = ['school_id','name','slug','description','icon','badge_type','criteria','points','status'];
    protected function casts(): array { return ['criteria'=>'array','points'=>'integer']; }
    public function studentBadges(): HasMany { return $this->hasMany(StudentBadge::class); }
    public function scopeActive($q) { return $q->where('status','active'); }
    public function scopeGlobal($q) { return $q->whereNull('school_id'); }
    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
}