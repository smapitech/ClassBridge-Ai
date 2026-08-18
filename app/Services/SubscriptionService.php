<?php
namespace App\Services;

use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use App\Models\UsageCounter;

/**
 * Subscription and usage limit enforcement service.
 */
class SubscriptionService
{
    /** Check if a school can add more teachers */
    public function canAddTeacher(School $school): bool
    {
        $plan = $this->getActivePlan($school);
        if (!$plan || !$plan->max_teachers) return true;
        $current = $school->teachers()->count();
        return $current < $plan->max_teachers;
    }

    /** Check if a school can add more students */
    public function canAddStudent(School $school): bool
    {
        $plan = $this->getActivePlan($school);
        if (!$plan || !$plan->max_students) return true;
        $current = $school->students()->count();
        return $current < $plan->max_students;
    }

    /** Check if a school can create more live classrooms */
    public function canCreateLiveClassroom(School $school): bool
    {
        $plan = $this->getActivePlan($school);
        if (!$plan || !$plan->max_live_classrooms) return true;
        $current = \App\Models\LiveClassroom::where('school_id', $school->id)->count();
        return $current < $plan->max_live_classrooms;
    }

    /** Check if a school can use AI generation */
    public function canUseAi(School $school): bool
    {
        $sub = $school->activeSubscription();
        if (!$sub) return false;
        $plan = $sub->subscriptionPlan;
        if (!$plan || !$plan->ai_requests_per_month) return true;
        return $sub->ai_requests_used < $plan->ai_requests_per_month;
    }

    /** Increment AI usage for a school */
    public function incrementAiUsage(School $school): void
    {
        $sub = $school->activeSubscription();
        if ($sub) {
            $sub->increment('ai_requests_used');
        }
    }

    /** Get active subscription plan for a school */
    public function getActivePlan(School $school): ?SubscriptionPlan
    {
        $sub = $school->activeSubscription();
        return $sub?->subscriptionPlan;
    }

    /** Get or create usage counter for a school this month */
    public function getUsageCounter(School $school): UsageCounter
    {
        return UsageCounter::firstOrCreate(
            ['school_id' => $school->id],
            ['period_start' => now()->startOfMonth(), 'period_end' => now()->endOfMonth()]
        );
    }

    /** Get friendly limit message */
    public function limitMessage(string $resource): string
    {
        return match ($resource) {
            'teacher' => 'You have reached the maximum number of teachers for your plan. Please upgrade to add more teachers.',
            'student' => 'You have reached the maximum number of students for your plan. Please upgrade to add more students.',
            'live_classroom' => 'You have reached the classroom limit for your plan. Please upgrade to create more classrooms.',
            'ai' => 'Your school has reached the monthly AI generation limit. Please contact your school admin or upgrade your plan.',
            default => 'Limit reached. Please upgrade your plan.',
        };
    }
}