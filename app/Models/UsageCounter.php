<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageCounter extends Model
{
    protected $fillable = ['school_id','subscription_plan_id','teachers_count','students_count','live_classrooms_count','ai_generations_count','storage_used_mb','period_start','period_end'];
    protected function casts(): array { return ['period_start'=>'datetime','period_end'=>'datetime','storage_used_mb'=>'decimal:2']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}