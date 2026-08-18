<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIPromptTemplate extends Model
{
    protected $table = 'ai_prompt_templates';

    protected $fillable = [
        'school_id', 'name', 'slug', 'type', 'subject', 'age_group',
        'template', 'status', 'created_by',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeOfType($q, string $type) { return $q->where('type', $type); }
    public function scopeGlobal($q) { return $q->whereNull('school_id'); }
}