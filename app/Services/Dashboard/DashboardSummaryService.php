<?php

namespace App\Services\Dashboard;

use App\Models\AIGeneration;
use App\Models\AIProvider;
use App\Models\CodeProject;
use App\Models\CodingAssignment;
use App\Models\CodingAssignmentSubmission;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\LessonReplay;
use App\Models\LiveClassroom;
use App\Models\ParentReport;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\StudentAchievement;
use App\Models\StudentBadge;
use App\Models\StudentCertificate;
use App\Models\StudentPoint;
use App\Models\SubscriptionPlan;
use App\Models\TeacherFeedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class DashboardSummaryService
{
    protected function tableExists(string $modelClass): bool
    {
        try {
            $model = app($modelClass);
            $table = method_exists($model, 'getTable') ? $model->getTable() : null;

            return $table ? Schema::hasTable($table) : false;
        } catch (Throwable) {
            return false;
        }
    }

    public function countSafely(string $modelClass, ?callable $callback = null): int
    {
        if (!$this->tableExists($modelClass)) {
            return 0;
        }

        try {
            $query = $modelClass::query();

            if ($callback) {
                $query = $callback($query);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    public function collectionSafely(string $modelClass, callable $callback): Collection
    {
        if (!$this->tableExists($modelClass)) {
            return collect();
        }

        try {
            $result = $callback($modelClass::query());

            return $result instanceof Collection ? $result : collect($result);
        } catch (Throwable) {
            return collect();
        }
    }

    protected function relationCollection(callable $callback): Collection
    {
        try {
            $result = $callback();

            return $result instanceof Collection ? $result : collect($result);
        } catch (Throwable) {
            return collect();
        }
    }

    protected function organizationTypeCounts(): array
    {
        $types = [
            'school' => 'Schools',
            'tutoring_center' => 'Tutoring centers',
            'coding_academy' => 'Coding academies',
            'private_tutor' => 'Private tutors',
            'homeschool' => 'Homeschool tutors',
            'online_academy' => 'Online lesson businesses',
            'other' => 'Other organizations',
        ];

        $breakdown = [];

        foreach ($types as $type => $label) {
            $count = $this->countSafely(School::class, fn (Builder $query) => $query->where('organization_type', $type));

            $breakdown[] = [
                'key' => $type,
                'label' => $label,
                'count' => $count,
            ];
        }

        return $breakdown;
    }

    protected function recentOrganizations(int $limit = 6): Collection
    {
        return $this->collectionSafely(School::class, function (Builder $query) use ($limit) {
            return $query->with(['owner'])->withCount('users')->latest()->take($limit)->get();
        });
    }

    protected function recentLiveSessions(array $filters = [], int $limit = 6): Collection
    {
        return $this->collectionSafely(LiveClassroom::class, function (Builder $query) use ($filters, $limit) {
            foreach ($filters as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }

            return $query->with(['school', 'teacher', 'classe', 'subject'])
                ->latest('scheduled_at')
                ->take($limit)
                ->get();
        });
    }

    protected function schoolSubscriptionSummary(?School $school): array
    {
        if (!$school) {
            return [
                'subscription' => null,
                'plan' => null,
                'status_label' => 'No organization linked',
                'ai_requests_used' => 0,
                'ai_requests_limit' => 0,
                'feature_flags' => [],
            ];
        }

        try {
            $subscription = $school->activeSubscription();
            $plan = $subscription?->subscriptionPlan;

            return [
                'subscription' => $subscription,
                'plan' => $plan,
                'status_label' => $subscription?->isOnTrial()
                    ? 'Trial'
                    : ($subscription?->isActive() ? 'Active' : ucfirst($subscription?->status ?? 'Inactive')),
                'ai_requests_used' => (int) ($subscription?->ai_requests_used ?? 0),
                'ai_requests_limit' => (int) ($plan?->ai_requests_per_month ?? 0),
                'feature_flags' => [
                    ['label' => 'Whiteboard', 'enabled' => (bool) ($plan?->has_whiteboard ?? false)],
                    ['label' => 'Code editor', 'enabled' => (bool) ($plan?->has_code_editor ?? false)],
                    ['label' => 'AI tools', 'enabled' => (bool) ($plan?->has_ai_assistant ?? false)],
                    ['label' => 'Parent reports', 'enabled' => (bool) ($plan?->has_parent_reports ?? false)],
                ],
            ];
        } catch (Throwable) {
            return [
                'subscription' => null,
                'plan' => null,
                'status_label' => 'Unavailable',
                'ai_requests_used' => 0,
                'ai_requests_limit' => 0,
                'feature_flags' => [],
            ];
        }
    }

    protected function schoolRelationCounts(?School $school): array
    {
        if (!$school) {
            return [
                'teachers_tutors' => 0,
                'students_learners' => 0,
                'parents' => 0,
                'homework_count' => 0,
                'quiz_count' => 0,
                'live_sessions' => 0,
                'upcoming_sessions' => 0,
                'ai_usage_this_month' => 0,
                'lesson_replays' => 0,
            ];
        }

        $schoolId = $school->id;

        return [
            'teachers_tutors' => $this->countSafely(User::class, fn (Builder $query) => $query
                ->where('school_id', $schoolId)
                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->whereIn('slug', ['teacher', 'school_admin', 'school_owner']))),
            'students_learners' => $this->countSafely(User::class, fn (Builder $query) => $query
                ->where('school_id', $schoolId)
                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'student'))),
            'parents' => $this->countSafely(User::class, fn (Builder $query) => $query
                ->where('school_id', $schoolId)
                ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'parent'))),
            'homework_count' => $this->countSafely(Homework::class, fn (Builder $query) => $query->where('school_id', $schoolId)),
            'quiz_count' => $this->countSafely(Quiz::class, fn (Builder $query) => $query->where('school_id', $schoolId)),
            'live_sessions' => $this->countSafely(LiveClassroom::class, fn (Builder $query) => $query
                ->where('school_id', $schoolId)
                ->where('status', 'live')),
            'upcoming_sessions' => $this->countSafely(LiveClassroom::class, fn (Builder $query) => $query
                ->where('school_id', $schoolId)
                ->whereIn('status', ['scheduled', 'live'])),
            'ai_usage_this_month' => $this->countSafely(AIGeneration::class, fn (Builder $query) => $query
                ->where('school_id', $schoolId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)),
            'lesson_replays' => $this->countSafely(LessonReplay::class, fn (Builder $query) => $query->where('school_id', $schoolId)),
        ];
    }

    protected function teacherClasses(User $user): Collection
    {
        return $this->relationCollection(function () use ($user) {
            return $user->classesAsTeacher()
                ->with(['students'])
                ->withCount('students')
                ->latest()
                ->get();
        });
    }

    protected function studentClasses(User $user): Collection
    {
        return $this->relationCollection(function () use ($user) {
            return $user->classesAsStudent()
                ->with(['teachers', 'subjects'])
                ->latest()
                ->get();
        });
    }

    protected function parentChildren(User $user): Collection
    {
        return $this->relationCollection(function () use ($user) {
            return $user->children()
                ->with(['school'])
                ->get();
        });
    }

    protected function liveSessionsForTeacher(User $user, array $statuses = ['scheduled', 'live'], int $limit = 5): Collection
    {
        return $this->collectionSafely(LiveClassroom::class, function (Builder $query) use ($user, $statuses, $limit) {
            return $query->where('teacher_id', $user->id)
                ->whereIn('status', $statuses)
                ->with(['teacher', 'classe', 'subject'])
                ->latest('scheduled_at')
                ->take($limit)
                ->get();
        });
    }

    protected function liveSessionsForSchool(?School $school, array $statuses = ['scheduled', 'live'], int $limit = 5): Collection
    {
        if (!$school) {
            return collect();
        }

        return $this->collectionSafely(LiveClassroom::class, function (Builder $query) use ($school, $statuses, $limit) {
            return $query->where('school_id', $school->id)
                ->whereIn('status', $statuses)
                ->with(['teacher', 'classe', 'subject'])
                ->latest('scheduled_at')
                ->take($limit)
                ->get();
        });
    }

    protected function liveSessionsForStudent(Collection $classIds, array $statuses = ['scheduled', 'live'], int $limit = 5): Collection
    {
        if ($classIds->isEmpty()) {
            return collect();
        }

        return $this->collectionSafely(LiveClassroom::class, function (Builder $query) use ($classIds, $statuses, $limit) {
            return $query->whereIn('class_id', $classIds->all())
                ->whereIn('status', $statuses)
                ->with(['teacher', 'subject', 'classe'])
                ->latest('scheduled_at')
                ->take($limit)
                ->get();
        });
    }

    protected function childClassIds(Collection $children): Collection
    {
        $classIds = collect();

        foreach ($children as $child) {
            try {
                $classIds = $classIds->merge($child->classesAsStudent()->pluck('classes.id'));
            } catch (Throwable) {
                continue;
            }
        }

        return $classIds->filter()->unique()->values();
    }

    protected function normalizeSubmissions(Collection $homeworkSubmissions, Collection $codingSubmissions): Collection
    {
        $homework = $homeworkSubmissions->map(function ($submission) {
            return [
                'type' => 'Homework',
                'title' => $submission->homework?->title ?? 'Homework submission',
                'student' => $submission->student?->displayName() ?? 'Learner',
                'score' => $submission->score,
                'status' => $submission->status,
                'note' => Str::limit((string) ($submission->teacher_feedback ?? ''), 90),
                'submitted_at' => $submission->submitted_at ?? $submission->created_at,
            ];
        });

        $coding = $codingSubmissions->map(function ($submission) {
            return [
                'type' => 'Coding',
                'title' => $submission->assignment?->title ?? 'Coding submission',
                'student' => $submission->student?->displayName() ?? 'Learner',
                'score' => $submission->score,
                'status' => $submission->status,
                'note' => Str::limit((string) ($submission->teacher_feedback ?? ''), 90),
                'submitted_at' => $submission->submitted_at ?? $submission->created_at,
            ];
        });

        return $homework->merge($coding)
            ->sortByDesc(fn (array $item) => $item['submitted_at']?->timestamp ?? 0)
            ->values();
    }

    protected function normalizeAssignments(Collection $homeworks, Collection $codingAssignments): Collection
    {
        $homeworkItems = $homeworks->map(function ($homework) {
            return [
                'type' => 'Homework',
                'title' => $homework->title ?? 'Homework assignment',
                'class' => $homework->classe?->name ?? 'All learners',
                'subject' => $homework->subject?->name ?? null,
                'status' => $homework->status ?? 'draft',
                'due_at' => $homework->due_at ?? $homework->created_at,
            ];
        });

        $codingItems = $codingAssignments->map(function ($assignment) {
            return [
                'type' => 'Coding',
                'title' => $assignment->title ?? 'Coding assignment',
                'class' => $assignment->classe?->name ?? 'All learners',
                'subject' => $assignment->subject?->name ?? null,
                'status' => $assignment->status ?? 'draft',
                'due_at' => $assignment->due_at ?? $assignment->created_at,
            ];
        });

        return $homeworkItems->merge($codingItems)
            ->sortByDesc(fn (array $item) => $item['due_at']?->timestamp ?? 0)
            ->values();
    }

    public function superAdmin(): array
    {
        $organizationBreakdown = collect($this->organizationTypeCounts());

        return [
            'stats' => [
                'total_organizations' => $this->countSafely(School::class),
                'schools_count' => data_get($organizationBreakdown->firstWhere('key', 'school'), 'count', 0),
                'private_tutors_count' => data_get($organizationBreakdown->firstWhere('key', 'private_tutor'), 'count', 0),
                'tutoring_centers_count' => data_get($organizationBreakdown->firstWhere('key', 'tutoring_center'), 'count', 0),
                'active_live_sessions' => $this->countSafely(LiveClassroom::class, fn (Builder $query) => $query->where('status', 'live')),
                'total_teachers_tutors' => $this->countSafely(User::class, fn (Builder $query) => $query
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->whereIn('slug', ['teacher', 'school_admin', 'school_owner']))),
                'total_students_learners' => $this->countSafely(User::class, fn (Builder $query) => $query
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'student'))),
                'total_parents' => $this->countSafely(User::class, fn (Builder $query) => $query
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'parent'))),
                'ai_usage_total' => $this->countSafely(AIGeneration::class),
                'ai_usage_this_month' => $this->countSafely(AIGeneration::class, fn (Builder $query) => $query
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)),
                'ai_providers' => $this->countSafely(AIProvider::class),
                'subscription_plans' => $this->countSafely(SubscriptionPlan::class),
                'active_subscription_plans' => $this->countSafely(SubscriptionPlan::class, fn (Builder $query) => $query->active()),
                'active_organization_subscriptions' => $this->countSafely(SchoolSubscription::class, fn (Builder $query) => $query->whereIn('status', ['active', 'trial'])),
                'trial_organization_subscriptions' => $this->countSafely(SchoolSubscription::class, fn (Builder $query) => $query->where('status', 'trial')),
            ],
            'organizationBreakdown' => $organizationBreakdown,
            'recentOrganizations' => $this->recentOrganizations(),
            'recentLiveSessions' => $this->recentLiveSessions(['status' => ['live', 'scheduled']]),
            'subscriptionSummary' => [
                'plans' => $this->countSafely(SubscriptionPlan::class),
                'active_plans' => $this->countSafely(SubscriptionPlan::class, fn (Builder $query) => $query->active()),
                'active_subscriptions' => $this->countSafely(SchoolSubscription::class, fn (Builder $query) => $query->whereIn('status', ['active', 'trial'])),
                'trial_subscriptions' => $this->countSafely(SchoolSubscription::class, fn (Builder $query) => $query->where('status', 'trial')),
            ],
            'aiSummary' => [
                'total' => $this->countSafely(AIGeneration::class),
                'this_month' => $this->countSafely(AIGeneration::class, fn (Builder $query) => $query
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)),
                'providers' => $this->countSafely(AIProvider::class),
            ],
        ];
    }

    public function organizationOwner(?User $user): array
    {
        $school = $user?->school;
        $counts = $this->schoolRelationCounts($school);
        $subscription = $this->schoolSubscriptionSummary($school);
        $upcomingSessions = $this->liveSessionsForSchool($school, ['scheduled', 'live'], 6);
        $recentSessions = $this->liveSessionsForSchool($school, ['live', 'ended', 'scheduled'], 6);

        $pendingHomeworkSubmissions = $school ? $this->collectionSafely(HomeworkSubmission::class, function (Builder $query) use ($school) {
            return $query->where('school_id', $school->id)
                ->where('status', 'submitted')
                ->with(['student', 'homework'])
                ->latest()
                ->take(5)
                ->get();
        }) : collect();

        $pendingCodingSubmissions = $school ? $this->collectionSafely(CodingAssignmentSubmission::class, function (Builder $query) use ($school) {
            return $query->where('school_id', $school->id)
                ->where('status', 'submitted')
                ->with(['student', 'assignment'])
                ->latest()
                ->take(5)
                ->get();
        }) : collect();

        $pendingHomeworkAssignments = $school ? $this->collectionSafely(Homework::class, function (Builder $query) use ($school) {
            return $query->where('school_id', $school->id)
                ->whereIn('status', ['draft', 'published'])
                ->with(['classe', 'subject'])
                ->latest()
                ->take(4)
                ->get();
        }) : collect();

        $pendingCodingAssignments = $school ? $this->collectionSafely(CodingAssignment::class, function (Builder $query) use ($school) {
            return $query->where('school_id', $school->id)
                ->whereIn('status', ['draft', 'published'])
                ->with(['classe', 'subject'])
                ->latest()
                ->take(4)
                ->get();
        }) : collect();

        return [
            'user' => $user,
            'school' => $school,
            'stats' => [
                'teachers_tutors' => $counts['teachers_tutors'],
                'students_learners' => $counts['students_learners'],
                'parents' => $counts['parents'],
                'active_live_sessions' => $counts['live_sessions'],
                'upcoming_sessions' => $counts['upcoming_sessions'],
                'homework_count' => $counts['homework_count'],
                'quiz_count' => $counts['quiz_count'],
                'ai_usage_this_month' => $counts['ai_usage_this_month'],
                'lesson_replays' => $counts['lesson_replays'],
            ],
            'subscription' => $subscription,
            'nextSession' => $upcomingSessions->first(),
            'recentTeachers' => $school ? $this->collectionSafely(User::class, function (Builder $query) use ($school) {
                return $query->where('school_id', $school->id)
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->whereIn('slug', ['teacher', 'school_admin', 'school_owner']))
                    ->with('role')
                    ->latest()
                    ->take(5)
                    ->get();
            }) : collect(),
            'recentStudents' => $school ? $this->collectionSafely(User::class, function (Builder $query) use ($school) {
                return $query->where('school_id', $school->id)
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'student'))
                    ->with('role')
                    ->latest()
                    ->take(5)
                    ->get();
            }) : collect(),
            'recentLearners' => $school ? $this->collectionSafely(User::class, function (Builder $query) use ($school) {
                return $query->where('school_id', $school->id)
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'student'))
                    ->with('role')
                    ->latest()
                    ->take(5)
                    ->get();
            }) : collect(),
            'recentParents' => $school ? $this->collectionSafely(User::class, function (Builder $query) use ($school) {
                return $query->where('school_id', $school->id)
                    ->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('slug', 'parent'))
                    ->with('role')
                    ->latest()
                    ->take(5)
                    ->get();
            }) : collect(),
            'upcomingSessions' => $upcomingSessions,
            'recentSessions' => $recentSessions,
            'pendingAssignments' => $this->normalizeAssignments($pendingHomeworkAssignments, $pendingCodingAssignments),
            'workAwaitingReview' => $this->normalizeSubmissions($pendingHomeworkSubmissions, $pendingCodingSubmissions),
        ];
    }

    public function teacher(?User $user): array
    {
        $school = $user?->school;
        $classes = $user ? $this->teacherClasses($user) : collect();
        $myStudents = $classes->pluck('students')->flatten()->unique('id')->values();
        $studentIds = $myStudents->pluck('id')->values();
        $upcomingSessions = $user ? $this->liveSessionsForTeacher($user, ['scheduled', 'live'], 5) : collect();
        $recentSessions = $user ? $this->liveSessionsForTeacher($user, ['live', 'ended', 'scheduled'], 5) : collect();
        $nextSession = $upcomingSessions->first();

        $homeworkSubmissions = $school ? $this->collectionSafely(HomeworkSubmission::class, function (Builder $query) use ($school, $user) {
            return $query->where('school_id', $school->id)
                ->where('status', 'submitted')
                ->whereHas('homework', fn (Builder $homeworkQuery) => $homeworkQuery->where('teacher_id', $user->id))
                ->with(['student', 'homework'])
                ->latest()
                ->take(5)
                ->get();
        }) : collect();

        $codingSubmissions = $school ? $this->collectionSafely(CodingAssignmentSubmission::class, function (Builder $query) use ($school, $user) {
            return $query->where('school_id', $school->id)
                ->whereHas('assignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('teacher_id', $user->id))
                ->with(['student', 'assignment'])
                ->latest()
                ->take(5)
                ->get();
        }) : collect();

        $homeworkAssignments = $school ? $this->collectionSafely(Homework::class, function (Builder $query) use ($school, $user) {
            return $query->where('school_id', $school->id)
                ->where('teacher_id', $user->id)
                ->whereIn('status', ['draft', 'published'])
                ->with(['classe', 'subject'])
                ->latest()
                ->take(4)
                ->get();
        }) : collect();

        $codingAssignments = $school ? $this->collectionSafely(CodingAssignment::class, function (Builder $query) use ($school, $user) {
            return $query->where('school_id', $school->id)
                ->where('teacher_id', $user->id)
                ->whereIn('status', ['draft', 'published'])
                ->with(['classe', 'subject'])
                ->latest()
                ->take(4)
                ->get();
        }) : collect();

        return [
            'user' => $user,
            'school' => $school,
            'classes' => $classes,
            'myStudents' => $myStudents,
            'studentIds' => $studentIds,
            'nextSession' => $nextSession,
            'upcomingSessions' => $upcomingSessions,
            'recentSessions' => $recentSessions,
            'pendingHomeworkReviews' => $homeworkSubmissions->count(),
            'pendingCodingReviews' => $codingSubmissions->count(),
            'recentStudentSubmissions' => $this->normalizeSubmissions($homeworkSubmissions, $codingSubmissions),
            'pendingAssignments' => $this->normalizeAssignments($homeworkAssignments, $codingAssignments),
            'stats' => [
                'my_classes' => $classes->count(),
                'my_students' => $myStudents->count(),
                'live_sessions' => $school ? $this->countSafely(LiveClassroom::class, fn (Builder $query) => $query
                    ->where('teacher_id', $user->id)
                    ->where('status', 'live')) : 0,
                'upcoming_sessions' => $upcomingSessions->count(),
                'pending_reviews' => $homeworkSubmissions->count() + $codingSubmissions->count(),
                'ai_usage_this_month' => $school ? $this->countSafely(AIGeneration::class, fn (Builder $query) => $query
                    ->where('school_id', $school->id)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)) : 0,
            ],
        ];
    }

    public function student(?User $user): array
    {
        $school = $user?->school;
        $classes = $user ? $this->studentClasses($user) : collect();
        $classIds = $classes->pluck('id')->values();
        $upcomingSessions = $this->liveSessionsForStudent($classIds, ['scheduled', 'live'], 5);
        $nextSession = $upcomingSessions->first();
        $currentTeacher = $nextSession?->teacher ?? $classes->flatMap(fn ($class) => $class->teachers)->first();

        $homeworkCount = $school ? $this->countSafely(Homework::class, fn (Builder $query) => $query
            ->where('school_id', $school->id)
            ->where('status', 'published')
            ->when($classIds->isNotEmpty(), fn (Builder $classQuery) => $classQuery->whereIn('class_id', $classIds->all()))) : 0;

        $quizCount = $school ? $this->countSafely(Quiz::class, fn (Builder $query) => $query
            ->where('school_id', $school->id)
            ->where('status', 'published')
            ->when($classIds->isNotEmpty(), fn (Builder $classQuery) => $classQuery->whereIn('class_id', $classIds->all()))) : 0;

        $recentQuizAttempts = $this->collectionSafely(QuizAttempt::class, function (Builder $query) use ($user) {
            return $query->where('student_id', $user->id)
                ->with('quiz')
                ->latest()
                ->take(5)
                ->get();
        });

        $recentFeedback = $this->collectionSafely(TeacherFeedback::class, function (Builder $query) use ($user) {
            return $query->where('student_id', $user->id)
                ->with(['teacher', 'classe'])
                ->latest()
                ->take(5)
                ->get();
        });

        $recentProjects = $this->collectionSafely(CodeProject::class, function (Builder $query) use ($user, $school) {
            return $query->where('student_id', $user->id)
                ->when($school, fn (Builder $schoolQuery) => $schoolQuery->where('school_id', $school->id))
                ->with(['teacher'])
                ->latest()
                ->take(5)
                ->get();
        });

        $badges = $this->collectionSafely(StudentBadge::class, function (Builder $query) use ($user) {
            return $query->where('student_id', $user->id)
                ->with('badge')
                ->latest()
                ->take(4)
                ->get();
        });

        $certificates = $this->collectionSafely(StudentCertificate::class, function (Builder $query) use ($user) {
            return $query->where('student_id', $user->id)
                ->latest()
                ->take(4)
                ->get();
        });

        $points = $this->collectionSafely(StudentPoint::class, function (Builder $query) use ($user) {
            return $query->where('student_id', $user->id)->get();
        });

        return [
            'user' => $user,
            'school' => $school,
            'classes' => $classes,
            'classIds' => $classIds,
            'upcomingSessions' => $upcomingSessions,
            'nextSession' => $nextSession,
            'currentTeacher' => $currentTeacher,
            'homeworkCount' => $homeworkCount,
            'quizCount' => $quizCount,
            'recentQuizAttempts' => $recentQuizAttempts,
            'recentFeedback' => $recentFeedback,
            'recentProjects' => $recentProjects,
            'badges' => $badges,
            'certificates' => $certificates,
            'learningPoints' => (int) $points->sum('points'),
            'stats' => [
                'upcoming_sessions' => $upcomingSessions->count(),
                'homework_count' => $homeworkCount,
                'quiz_count' => $quizCount,
                'points' => (int) $points->sum('points'),
                'badges' => $badges->count(),
                'certificates' => $certificates->count(),
                'projects' => $recentProjects->count(),
            ],
        ];
    }

    public function parent(?User $user): array
    {
        $school = $user?->school;
        $children = $user ? $this->parentChildren($user) : collect();
        $childIds = $children->pluck('id')->values();
        $childClassIds = $this->childClassIds($children);
        $upcomingSessions = $this->liveSessionsForStudent($childClassIds, ['scheduled', 'live'], 5);

        $teacherFeedback = $this->collectionSafely(TeacherFeedback::class, function (Builder $query) use ($childIds) {
            return $query->whereIn('student_id', $childIds->all())
                ->with(['teacher', 'student', 'classe'])
                ->latest()
                ->take(5)
                ->get();
        });

        $publishedReports = $this->collectionSafely(ParentReport::class, function (Builder $query) use ($childIds) {
            return $query->whereIn('student_id', $childIds->all())
                ->where('status', 'published')
                ->with(['student', 'classe'])
                ->latest()
                ->take(5)
                ->get();
        });

        $lessonReplays = $school ? $this->collectionSafely(LessonReplay::class, function (Builder $query) use ($school) {
            return $query->where('school_id', $school->id)
                ->with(['classroom', 'session'])
                ->latest()
                ->take(4)
                ->get();
        }) : collect();

        $achievements = $this->collectionSafely(StudentAchievement::class, function (Builder $query) use ($childIds) {
            return $query->whereIn('student_id', $childIds->all())
                ->latest()
                ->take(5)
                ->get();
        });

        $quizAttempts = $this->collectionSafely(QuizAttempt::class, function (Builder $query) use ($childIds) {
            return $query->whereIn('student_id', $childIds->all())
                ->with(['quiz', 'student'])
                ->latest()
                ->take(6)
                ->get();
        });

        $childSummaries = $children->map(function (User $child) use ($school) {
            $classIds = $this->relationCollection(function () use ($child) {
                return $child->classesAsStudent()->pluck('classes.id');
            })->values();

            return [
                'child' => $child,
                'name' => $child->displayName(),
                'email' => $child->email,
                'school' => $child->school?->displayLabel(),
                'class_count' => $classIds->count(),
                'homework_count' => $this->countSafely(Homework::class, fn (Builder $query) => $query
                    ->where('school_id', $school?->id)
                    ->where('status', 'published')
                    ->when($classIds->isNotEmpty(), fn (Builder $classQuery) => $classQuery->whereIn('class_id', $classIds->all()))),
                'quiz_count' => $this->countSafely(Quiz::class, fn (Builder $query) => $query
                    ->where('school_id', $school?->id)
                    ->where('status', 'published')
                    ->when($classIds->isNotEmpty(), fn (Builder $classQuery) => $classQuery->whereIn('class_id', $classIds->all()))),
                'feedback_count' => $this->countSafely(TeacherFeedback::class, fn (Builder $query) => $query->where('student_id', $child->id)),
                'achievement_count' => $this->countSafely(StudentAchievement::class, fn (Builder $query) => $query->where('student_id', $child->id)),
                'certificate_count' => $this->countSafely(StudentCertificate::class, fn (Builder $query) => $query->where('student_id', $child->id)),
                'badge_count' => $this->countSafely(StudentBadge::class, fn (Builder $query) => $query->where('student_id', $child->id)),
            ];
        });

        return [
            'user' => $user,
            'school' => $school,
            'children' => $children,
            'childIds' => $childIds,
            'childClassIds' => $childClassIds,
            'upcomingSessions' => $upcomingSessions,
            'teacherFeedback' => $teacherFeedback,
            'publishedReports' => $publishedReports,
            'lessonReplays' => $lessonReplays,
            'achievements' => $achievements,
            'quizAttempts' => $quizAttempts,
            'childSummaries' => $childSummaries,
            'stats' => [
                'linked_children' => $children->count(),
                'upcoming_sessions' => $upcomingSessions->count(),
                'homework_items' => $childClassIds->isNotEmpty()
                    ? $this->countSafely(Homework::class, fn (Builder $query) => $query
                        ->where('school_id', $school?->id)
                        ->where('status', 'published')
                        ->whereIn('class_id', $childClassIds->all()))
                    : 0,
                'quiz_scores' => $quizAttempts->count(),
                'feedback_items' => $teacherFeedback->count(),
                'reports' => $publishedReports->count(),
                'achievements' => $achievements->count(),
                'lesson_replays' => $lessonReplays->count(),
            ],
        ];
    }
}
