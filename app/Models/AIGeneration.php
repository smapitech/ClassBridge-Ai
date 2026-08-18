<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGeneration extends Model
{
    protected $table = 'ai_generations';

    protected $fillable = [
        'school_id', 'user_id', 'provider_id', 'model', 'type', 'title',
        'prompt', 'response', 'status', 'error_message',
        'tokens_input', 'tokens_output', 'total_tokens',
        'cost_estimate', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'cost_estimate' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function provider(): BelongsTo { return $this->belongsTo(AIProvider::class, 'provider_id'); }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
    public function scopeFailed($q) { return $q->where('status', 'failed'); }
    public function scopeOfType($q, string $type) { return $q->where('type', $type); }
}