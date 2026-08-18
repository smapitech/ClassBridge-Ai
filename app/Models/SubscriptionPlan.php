<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'max_students',
        'max_teachers',
        'max_classes',
        'ai_requests_per_month',
        'has_whiteboard',
        'has_code_editor',
        'has_ai_assistant',
        'has_attendance',
        'has_homework',
        'has_parent_reports',
        'is_active',
        'sort_order',
        'currency',
        'max_live_classrooms',
        'max_storage_mb',
        'features',
        'is_popular',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_active' => 'boolean',
            'has_whiteboard' => 'boolean',
            'has_code_editor' => 'boolean',
            'has_ai_assistant' => 'boolean',
            'has_attendance' => 'boolean',
            'has_homework' => 'boolean',
            'has_parent_reports' => 'boolean',
        ];
    }

    /**
     * School subscriptions using this plan.
     */
    public function schoolSubscriptions(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}