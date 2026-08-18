<?php namespace App\Http\Controllers\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\StudentAchievement;
use App\Models\StudentBadge;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    protected GamificationService $gamify;

    public function __construct(GamificationService $gamify)
    {
        $this->gamify = $gamify;
    }

    // ========== TEACHER ==========

    /** Award badge/points to a student */
    public function award(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:users,id',
            'badge_id' => 'nullable|exists:badges,id',
            'points' => 'nullable|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $student = User::findOrFail($r->student_id);
        if ($student->school_id !== Auth::user()->school_id) abort(403);

        if ($r->badge_id) {
            $badge = Badge::findOrFail($r->badge_id);
            $this->gamify->awardBadge($student, $badge, Auth::user(), $r->reason);
        }

        if ($r->points) {
            $this->gamify->awardPoints($student, $r->points, $r->reason ?? 'Teacher awarded');
            $this->gamify->logAchievement($student, 'teacher_award', 'Earned ' . $r->points . ' stars', $r->points);
        }

        return back()->with('success', 'Award given successfully!');
    }

    /** Student leaderboard */
    public function leaderboard(Request $r)
    {
        $schoolId = Auth::user()->school_id;
        $students = User::where('school_id', $schoolId)
            ->whereHas('role', fn($q) => $q->where('slug', 'student'))
            ->withSum('points', 'points')
            ->orderByDesc('points_sum_points')
            ->limit(30)->get();

        return view('gamification.leaderboard', compact('students'));
    }

    // ========== STUDENT ==========

    /** Student's own gamification dashboard */
    public function myProgress()
    {
        $student = Auth::user();
        $totalPoints = $this->gamify->getTotalPoints($student);
        $badges = $this->gamify->getStudentBadges($student);
        $currentLevel = $this->gamify->getCurrentLevel($student);
        $nextLevel = $this->gamify->getNextLevel($student);
        $achievements = StudentAchievement::where('student_id', $student->id)->latest()->limit(10)->get();
        $allBadges = Badge::active()->where(fn($q) => $q->whereNull('school_id')->orWhere('school_id', $student->school_id))->get();

        $progressPercent = 0;
        if ($currentLevel && $nextLevel) {
            $range = $nextLevel->min_points - $currentLevel->min_points;
            $progress = $totalPoints - $currentLevel->min_points;
            $progressPercent = $range > 0 ? min(100, round($progress / $range * 100)) : 100;
        }

        return view('gamification.my-progress', compact('totalPoints', 'badges', 'currentLevel', 'nextLevel', 'achievements', 'allBadges', 'progressPercent'));
    }

    // ========== PARENT ==========

    /** Parent view of child gamification */
    public function childProgress(User $student)
    {
        $parent = Auth::user();
        $childrenIds = $parent->parentLinks()->pluck('student_id');
        if (!$childrenIds->contains($student->id)) abort(403);

        $totalPoints = $this->gamify->getTotalPoints($student);
        $badges = $this->gamify->getStudentBadges($student);
        $currentLevel = $this->gamify->getCurrentLevel($student);
        $nextLevel = $this->gamify->getNextLevel($student);
        $achievements = StudentAchievement::where('student_id', $student->id)->latest()->limit(10)->get();

        return view('gamification.parent-view', compact('student', 'totalPoints', 'badges', 'currentLevel', 'nextLevel', 'achievements'));
    }
}