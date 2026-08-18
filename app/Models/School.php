<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'slug',
        'organization_type',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'address',
        'logo_path',
        'website',
        'owner_user_id',
        'status',
        'subscription_plan_id',
        'trial_ends_at',
        'timezone',
        'preferred_teaching_mode',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(\App\Models\Course::class);
    }

    public function activeSubscription(): ?SchoolSubscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }

    public function hasFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription();
        if (!$subscription) {
            return false;
        }
        return (bool) ($subscription->subscriptionPlan?->{$feature} ?? false);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDisplayNameAttribute($value): string
    {
        return $value ?: ($this->attributes['name'] ?? $this->name ?? '');
    }

    public function getOrganizationTypeAttribute($value): string
    {
        return $value ?: 'school';
    }

    public function getPreferredTeachingModeAttribute($value): string
    {
        return $value ?: 'whiteboard';
    }

    public function displayLabel(): string
    {
        return $this->display_name ?: $this->name;
    }

    public function organizationTypeLabel(): string
    {
        return classbridge_organization_type_label($this->organization_type);
    }

    public function preferredTeachingModeLabel(): string
    {
        return classbridge_preferred_teaching_mode_label($this->preferred_teaching_mode);
    }

    public function isPrivateTutorWorkspace(): bool
    {
        return in_array($this->organization_type, ['private_tutor', 'homeschool'], true);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    public function teachers()
    {
        return $this->users()->whereHas('role', fn($q) => $q->whereIn('slug', ['teacher', 'school_admin', 'school_owner']));
    }

    public function students()
    {
        return $this->users()->whereHas('role', fn($q) => $q->where('slug', 'student'));
    }

    public function parents()
    {
        return $this->users()->whereHas('role', fn($q) => $q->where('slug', 'parent'));
    }

    public function admins()
    {
        return $this->users()->whereHas('role', fn($q) => $q->whereIn('slug', ['school_owner', 'school_admin']));
    }

    /** Phase 5: AI Settings */
    public function aiSetting()
    {
        return $this->hasOne(\App\Models\AISetting::class);
    }
}
