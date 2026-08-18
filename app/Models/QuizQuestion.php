<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $fillable = ['school_id','quiz_id','question_text','question_type','options','correct_answer','marks','explanation','sort_order'];
    protected function casts(): array { return ['options'=>'array','marks'=>'decimal:2']; }
    public function quiz(): BelongsTo { return $this->belongsTo(Quiz::class); }
}