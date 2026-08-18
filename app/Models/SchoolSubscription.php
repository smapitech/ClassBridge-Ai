<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSubscription extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'school_id',
        'subscription_plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'ai_requests_used',
        'payment_method',
        'payment_reference',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * The school this subscription belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * The subscription plan this references.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Check if this subscription is currently active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }
        return true;
    }

    /**
     * Check if school is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    /**
     * Check remaining AI requests for this billing period.
     */
    public function remainingAiRequests(): int
    {
        $limit = $this->subscriptionPlan?->ai_requests_per_month ?? 0;
        return max(0, $limit - $this->ai_requests_used);
    }
}