<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIUsageLog extends Model
{
    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'school_id', 'user_id', 'provider_id', 'model', 'type',
        'tokens_input', 'tokens_output', 'total_tokens',
        'cost_estimate', 'request_status', 'error_message',
    ];

    protected function casts(): array
    {
        return ['cost_estimate' => 'decimal:6'];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function provider(): BelongsTo { return $this->belongsTo(AIProvider::class, 'provider_id'); }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
    public function scopeSuccess($q) { return $q->where('request_status', 'success'); }
    public function scopeFailed($q) { return $q->where('request_status', 'failed'); }
    public function scopeThisMonth($q) {
        return $q->where('created_at', '>=', now()->startOfMonth());
    }
}