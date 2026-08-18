<?php namespace App\Services;

use App\Models\Badge;
use App\Models\LearningLevel;
use App\Models\StudentAchievement;
use App\Models\StudentBadge;
use App\Models\StudentPoint;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GamificationService
{
    /** Award points to a student */
    public function awardPoints(User $student, int $points, string $reason, ?string $sourceType = null, $sourceId = null): void
    {
        StudentPoint::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'points' => $points,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reason' => $reason,
        ]);
    }

    /** Get total points for a student */
    public function getTotalPoints(User $student): int
    {
        return StudentPoint::where('student_id', $student->id)->sum('points');
    }

    /** Award a badge to a student */
    public function awardBadge(User $student, Badge $badge, ?User $awarder = null, ?string $reason = null): StudentBadge
    {
        // Check duplicate
        $existing = StudentBadge::where('student_id', $student->id)->where('badge_id', $badge->id)->first();
        if ($existing) return $existing;

        return StudentBadge::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'badge_id' => $badge->id,
            'awarded_by' => $awarder?->id ?? Auth::id(),
            'reason' => $reason,
            'awarded_at' => now(),
        ]);
    }

    /** Get badges earned by student */
    public function getStudentBadges(User $student)
    {
        return StudentBadge::where('student_id', $student->id)->with('badge')->latest()->get();
    }

    /** Get current learning level */
    public function getCurrentLevel(User $student): ?LearningLevel
    {
        $totalPoints = $this->getTotalPoints($student);
        return LearningLevel::where(function ($q) use ($student) {
            $q->whereNull('school_id')->orWhere('school_id', $student->school_id);
        })->active()->where('min_points', '<=', $totalPoints)
          ->orderBy('min_points', 'desc')->first();
    }

    /** Get next level */
    public function getNextLevel(User $student): ?LearningLevel
    {
        $totalPoints = $this->getTotalPoints($student);
        return LearningLevel::where(function ($q) use ($student) {
            $q->whereNull('school_id')->orWhere('school_id', $student->school_id);
        })->active()->where('min_points', '>', $totalPoints)
          ->orderBy('min_points')->first();
    }

    /** Create achievement record */
    public function logAchievement(User $student, string $type, string $title, int $points = 0, ?string $desc = null): void
    {
        StudentAchievement::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'achievement_type' => $type,
            'title' => $title,
            'description' => $desc,
            'points_awarded' => $points,
            'achieved_at' => now(),
        ]);
    }
}