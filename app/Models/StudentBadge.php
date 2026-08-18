<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBadge extends Model {
    protected $fillable = ['school_id','student_id','badge_id','awarded_by','reason','awarded_at','metadata'];
    protected function casts(): array { return ['awarded_at'=>'datetime','metadata'=>'array']; }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function badge(): BelongsTo { return $this->belongsTo(Badge::class); }
    public function awarder(): BelongsTo { return $this->belongsTo(User::class, 'awarded_by'); }
    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
}