<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPoint extends Model {
    protected $fillable = ['school_id','student_id','points','source_type','source_id','reason'];
    protected function casts(): array { return ['points'=>'integer']; }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
}