<?php

use Illuminate\Support\Facades\Schema;

if (!function_exists('safeCount')) {
    /**
     * Safely count a model, returning 0 if the table doesn't exist or query fails.
     */
    function safeCount(string $model, ?callable $query = null): int
    {
        try {
            $instance = app($model);
            $table = method_exists($instance, 'getTable') ? $instance->getTable() : null;

            if (!$table || !Schema::hasTable($table)) {
                return 0;
            }

            $q = $model::query();
            if ($query) {
                $q = $query($q);
            }
            return $q->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}

if (!function_exists('classbridge_role_label')) {
    function classbridge_role_label(?string $slug): string
    {
        return match ($slug) {
            'super_admin' => 'Super Admin',
            'school_owner' => 'Organization Owner',
            'school_admin' => 'Center Admin',
            'teacher' => 'Teacher / Tutor',
            'student' => 'Learner',
            'parent' => 'Parent',
            default => 'Workspace User',
        };
    }
}

if (!function_exists('classbridge_organization_type_label')) {
    function classbridge_organization_type_label(?string $type): string
    {
        return match ($type) {
            'school' => 'School',
            'tutoring_center' => 'Tutoring Center',
            'coding_academy' => 'Coding Academy',
            'private_tutor' => 'Private Tutor',
            'homeschool' => 'Homeschool Tutor',
            'online_academy' => 'Online Lesson Business',
            'other' => 'Other',
            default => 'School',
        };
    }
}

if (!function_exists('classbridge_preferred_teaching_mode_label')) {
    function classbridge_preferred_teaching_mode_label(?string $mode): string
    {
        return match (strtolower((string) $mode)) {
            'whiteboard' => 'Whiteboard Mode',
            'coding' => 'Coding Mode',
            'text', 'english', 'text/english', 'text-english' => 'Text / English Mode',
            'mathematics', 'math', 'maths' => 'Mathematics Mode',
            'presentation', 'slides' => 'Presentation Mode',
            default => 'Whiteboard Mode',
        };
    }
}

if (!function_exists('classbridge_role_badge_classes')) {
    function classbridge_role_badge_classes(?string $slug): string
    {
        return match ($slug) {
            'super_admin' => 'bg-violet-50 text-violet-700 border-violet-200',
            'school_owner' => 'bg-sky-50 text-sky-700 border-sky-200',
            'school_admin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'teacher' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'student' => 'bg-amber-50 text-amber-800 border-amber-200',
            'parent' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    }
}

if (!function_exists('classbridge_dashboard_route')) {
    function classbridge_dashboard_route(?string $slug): string
    {
        return match ($slug) {
            'super_admin' => route('super-admin.dashboard'),
            'school_owner', 'school_admin' => route('school.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'student' => route('student.dashboard'),
            'parent' => route('parent.dashboard'),
            default => route('no-role'),
        };
    }
}
