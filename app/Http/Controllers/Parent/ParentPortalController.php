<?php namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Badge;
use App\Models\Classe;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\ParentReport;
use App\Models\QuizAttempt;
use App\Models\School;
use App\Models\StudentAchievement;
use App\Models\StudentBadge;
use App\Models\StudentPoint;
use App\Models\TeacherFeedback;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentPortalController extends Controller
{
    protected GamificationService $gamify;

    public function __construct(GamificationService $gamify)
    {
        $this->gamify = $gamify;
    }

    /** Get parent's linked children */
    protected function myChildren()
    {
        return Auth::user()->children()->where('status','active')->get();
    }

    /** Parent Dashboard */
    public function dashboard(Request $r)
    {
        $parent = Auth::user();
        $children = $this->myChildren();
        $selectedChild = null;

        if ($r->child_id) {
            $selectedChild = $children->firstWhere('id', $r->child_id);
        }
        $selectedChild = $selectedChild ?? $children->first();

        if (!$selectedChild) {
            return view('parent-portal.empty', compact('children'));
        }

        return $this->childDashboard($selectedChild, $children);
    }

    /** Child-specific dashboard */
    protected function childDashboard(User $student, $allChildren)
    {
        $schoolId = Auth::user()->school_id;

        // Attendance
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)
            ->whereBetween('attendance_date', [now()->subDays(30), now()])->get();
        $attPresent = $attendanceRecords->where('status', 'present')->count();
        $attTotal = $attendanceRecords->count();

        // Homework
        $homeworkSubs = HomeworkSubmission::where('student_id', $student->id)->with('homework')->latest()->limit(10)->get();
        $homeworkDone = $homeworkSubs->where('status', 'reviewed')->count();

        // Quiz
        $quizAttempts = QuizAttempt::where('student_id', $student->id)->with('quiz')->latest()->limit(10)->get();
        $avgScore = $quizAttempts->avg('score') ?? 0;

        // Gamification
        $totalPoints = $this->gamify->getTotalPoints($student);
        $badges = $this->gamify->getStudentBadges($student);
        $currentLevel = $this->gamify->getCurrentLevel($student);
        $nextLevel = $this->gamify->getNextLevel($student);
        $achievements = StudentAchievement::where('student_id', $student->id)->latest()->limit(5)->get();

        $progressPercent = 0;
        if ($currentLevel && $nextLevel) {
            $range = $nextLevel->min_points - $currentLevel->min_points;
            $progress = $totalPoints - $currentLevel->min_points;
            $progressPercent = $range > 0 ? min(100, round($progress / $range * 100)) : 100;
        }

        // Teacher feedback
        $feedback = TeacherFeedback::where('student_id', $student->id)
            ->where('visibility', 'parent_visible')->with('teacher')->latest()->limit(5)->get();

        // Published reports
        $reports = ParentReport::where('student_id', $student->id)
            ->where('status', 'published')->latest()->limit(5)->get();

        // Upcoming classes
        $upcomingClass = Classe::where('school_id', $schoolId)
            ->whereHas('students', fn($q) => $q->where('student_id', $student->id))
            ->with('teacher')->first();

        return view('parent-portal.dashboard', compact(
            'student', 'allChildren', 'attendanceRecords', 'attPresent', 'attTotal',
            'homeworkSubs', 'homeworkDone', 'quizAttempts', 'avgScore',
            'totalPoints', 'badges', 'currentLevel', 'nextLevel', 'achievements', 'progressPercent',
            'feedback', 'reports', 'upcomingClass'
        ));
    }

    /** View a published report */
    public function viewReport(ParentReport $report)
    {
        $parent = Auth::user();
        $childrenIds = $parent->children()->pluck('id');
        if (!$childrenIds->contains($report->student_id)) abort(403);

        $report->load(['student', 'generator']);
        return view('parent-portal.report', compact('report'));
    }

    /** Generate smart report (teacher/admin) */
    public function generateReport(Request $r, User $student)
    {
        $teacher = Auth::user();
        if (!$teacher->isTeacher() && !$teacher->isSchoolAdmin() && !$teacher->isSchoolOwner()) abort(403);

        $schoolId = $teacher->school_id;
        if ($student->school_id !== $schoolId) abort(403);

        $start = $r->date('period_start', now()->subMonth());
        $end = $r->date('period_end', now());

        $att = AttendanceRecord::where('student_id', $student->id)->whereBetween('attendance_date', [$start, $end])->get();
        $hw = HomeworkSubmission::where('student_id', $student->id)->whereBetween('created_at', [$start, $end])->with('homework')->get();
        $qz = QuizAttempt::where('student_id', $student->id)->whereBetween('created_at', [$start, $end])->with('quiz')->get();
        $badges = StudentBadge::where('student_id', $student->id)->with('badge')->latest()->limit(5)->get();
        $points = StudentPoint::where('student_id', $student->id)->sum('points');

        // AI summary
        $aiSummary = null;
        if ($r->boolean('use_ai')) {
            try {
                $aiService = app(AIService::class);
                $prompt = "Write a parent-friendly summary for {$student->displayName()} covering: " .
                    "attendance ({$att->where('status','present')->count()}/{$att->count()} days present), " .
                    "homework ({$hw->where('status','reviewed')->count()}/{$hw->count()} submitted), " .
                    "quiz average ({$qz->avg('score')}%), " .
                    "points earned ($points), badges (" . $badges->pluck('badge.name')->join(', ') . "). " .
                    "Include strengths, areas to improve, and a home practice recommendation.";
                $aiSummary = $aiService->generate($prompt);
            } catch (\Exception $e) {
                $aiSummary = 'AI summary unavailable.';
            }
        }

        $report = ParentReport::create([
            'school_id' => $schoolId,
            'student_id' => $student->id,
            'report_type' => $r->input('report_type', 'custom'),
            'generated_by' => $teacher->id,
            'report_period_start' => $start,
            'report_period_end' => $end,
            'attendance_summary' => ['total' => count($att), 'present' => $att->where('status', 'present')->count(), 'absent' => $att->where('status', 'absent')->count(), 'late' => $att->where('status', 'late')->count()],
            'homework_summary' => ['total' => count($hw), 'submitted' => $hw->where('status', 'submitted')->count(), 'reviewed' => $hw->where('status', 'reviewed')->count(), 'avg_score' => $hw->avg('score')],
            'quiz_summary' => ['total' => count($qz), 'avg_score' => $qz->avg('score')],
            'badge_summary' => ['badges' => $badges->map(fn($b) => ['name' => $b->badge->name, 'icon' => $b->badge->icon])->toArray(), 'points' => $points],
            'teacher_comments' => $r->input('teacher_comments'),
            'ai_summary' => $aiSummary,
            'parent_recommendation' => $r->input('parent_recommendation'),
            'status' => $r->input('status', 'draft'),
        ]);

        if ($r->input('status') === 'published') {
            $report->update(['published_at' => now()]);
        }

        return redirect()->route('parent-portal.dashboard', ['child_id' => $student->id])
            ->with('success', 'Smart report generated successfully!');
    }

    /** Publish a report */
    public function publishReport(ParentReport $report)
    {
        if ($report->school_id !== Auth::user()->school_id) abort(403);
        $report->update(['status' => 'published', 'published_at' => now()]);
        return back()->with('success', 'Report published to parent.');
    }
}