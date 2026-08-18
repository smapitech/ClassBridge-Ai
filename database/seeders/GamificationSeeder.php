<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\LearningLevel;
use Illuminate\Database\Seeder;

class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        // -------- 10 Badges --------
        $badges = [
            ['name' => 'Homework Hero', 'slug' => 'homework-hero', 'badge_type' => 'homework', 'points' => 50, 'icon' => '📚', 'description' => 'Completed 10 homework assignments'],
            ['name' => 'Math Star', 'slug' => 'math-star', 'badge_type' => 'math', 'points' => 60, 'icon' => '⭐', 'description' => 'Score 90%+ in math quizzes'],
            ['name' => 'Reading Champion', 'slug' => 'reading-champion', 'badge_type' => 'reading', 'points' => 40, 'icon' => '📖', 'description' => 'Read 5 books and submitted reports'],
            ['name' => 'Code Builder', 'slug' => 'code-builder', 'badge_type' => 'coding', 'points' => 70, 'icon' => '💻', 'description' => 'Completed coding projects'],
            ['name' => 'Science Explorer', 'slug' => 'science-explorer', 'badge_type' => 'science', 'points' => 50, 'icon' => '🔬', 'description' => 'Completed science experiments'],
            ['name' => 'Perfect Attendance', 'slug' => 'perfect-attendance', 'badge_type' => 'attendance', 'points' => 30, 'icon' => '✅', 'description' => '10 consecutive days attendance'],
            ['name' => 'Creative Writer', 'slug' => 'creative-writer', 'badge_type' => 'reading', 'points' => 45, 'icon' => '✍️', 'description' => 'Wrote excellent English essays'],
            ['name' => 'Quiz Master', 'slug' => 'quiz-master', 'badge_type' => 'quiz', 'points' => 55, 'icon' => '🏆', 'description' => 'Scored 100% on a quiz'],
            ['name' => 'Good Effort', 'slug' => 'good-effort', 'badge_type' => 'behavior', 'points' => 25, 'icon' => '🌟', 'description' => 'Consistently tries hard in class'],
            ['name' => 'Fast Learner', 'slug' => 'fast-learner', 'badge_type' => 'custom', 'points' => 65, 'icon' => '🚀', 'description' => 'Completed tasks ahead of deadlines'],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(['slug' => $badge['slug']], array_merge($badge, ['status' => 'active']));
        }

        // -------- 5 Learning Levels --------
        $levels = [
            ['name' => 'Beginner Explorer', 'slug' => 'beginner-explorer', 'min_points' => 0, 'max_points' => 99, 'icon' => '🌱', 'sort_order' => 1, 'description' => 'Just starting the learning journey!'],
            ['name' => 'Rising Learner', 'slug' => 'rising-learner', 'min_points' => 100, 'max_points' => 249, 'icon' => '📈', 'sort_order' => 2, 'description' => 'Making progress, keep going!'],
            ['name' => 'Bright Star', 'slug' => 'bright-star', 'min_points' => 250, 'max_points' => 499, 'icon' => '⭐', 'sort_order' => 3, 'description' => 'Shining bright with knowledge!'],
            ['name' => 'Skill Builder', 'slug' => 'skill-builder', 'min_points' => 500, 'max_points' => 999, 'icon' => '🛠️', 'sort_order' => 4, 'description' => 'Building strong skills!'],
            ['name' => 'Class Champion', 'slug' => 'class-champion', 'min_points' => 1000, 'max_points' => null, 'icon' => '👑', 'sort_order' => 5, 'description' => 'Top of the class!'],
        ];

        foreach ($levels as $level) {
            LearningLevel::firstOrCreate(['slug' => $level['slug']], array_merge($level, ['status' => 'active']));
        }

        $this->command?->info('Gamification: 10 badges + 5 levels seeded!');
    }
}