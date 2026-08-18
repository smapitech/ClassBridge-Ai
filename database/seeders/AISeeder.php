<?php

namespace Database\Seeders;

use App\Models\AIProvider;
use App\Models\AISetting;
use App\Models\AIPromptTemplate;
use Illuminate\Database\Seeder;

class AISeeder extends Seeder
{
    public function run(): void
    {
        // -------- AI Providers --------
        $openai = AIProvider::firstOrCreate(
            ['slug' => 'openai'],
            [
                'name' => 'OpenAI',
                'slug' => 'openai',
                'provider_type' => 'openai',
                'status' => 'inactive',
                'base_url' => 'https://api.openai.com/v1',
                'default_model' => 'gpt-4o-mini',
                'available_models' => ['gpt-4o-mini', 'gpt-4o'],
                'supports_streaming' => true,
            ]
        );

        AIProvider::firstOrCreate(
            ['slug' => 'deepseek'],
            [
                'name' => 'DeepSeek',
                'slug' => 'deepseek',
                'provider_type' => 'deepseek',
                'status' => 'inactive',
                'base_url' => 'https://api.deepseek.com/v1',
                'default_model' => 'deepseek-v4-flash',
                'available_models' => ['deepseek-v4-flash', 'deepseek-v4-pro', 'deepseek-chat', 'deepseek-reasoner'],
                'supports_streaming' => false,
            ]
        );

        // -------- Global AI Settings --------
        if (!AISetting::global()) {
            AISetting::create([
                'school_id' => null,
                'ai_enabled' => true,
                'allow_teacher_ai' => true,
                'allow_school_override' => false,
                'monthly_generation_limit' => 1000,
                'monthly_token_limit' => null,
                'monthly_cost_limit' => null,
            ]);
        }

        // -------- Default Prompt Templates --------
        $templates = [
            ['name' => 'Math Lesson Plan (Age 6)', 'slug' => 'math-lesson-plan-6', 'type' => 'lesson_plan', 'subject' => 'Mathematics', 'age_group' => '6-7', 'template' => "Create a math lesson plan for a 6-year-old student on the topic: {{topic}}. Include:\n1. Learning objective\n2. Warm-up activity (5 min)\n3. Main teaching activity (15 min)\n4. Practice worksheet with 5 simple questions\n5. Fun closing game"],
            ['name' => 'English Lesson Plan (Age 6)', 'slug' => 'english-lesson-plan-6', 'type' => 'lesson_plan', 'subject' => 'English', 'age_group' => '6-7', 'template' => "Create an English lesson plan for a 6-year-old on: {{topic}}. Include:\n1. Phonics warm-up\n2. New vocabulary (5 words with examples)\n3. Reading activity\n4. Writing practice\n5. Closing song or rhyme"],
            ['name' => 'Coding Beginner HTML', 'slug' => 'coding-beginner-html', 'type' => 'lesson_plan', 'subject' => 'Coding', 'age_group' => '8-12', 'template' => "Create a beginner HTML coding lesson for ages 8-12 on: {{topic}}. Include step-by-step code examples, a mini-project, and common mistakes to avoid."],
            ['name' => 'Science Beginner Lesson', 'slug' => 'science-beginner', 'type' => 'lesson_plan', 'subject' => 'Science', 'age_group' => '6-10', 'template' => "Create an engaging science lesson for young learners on: {{topic}}. Include a simple experiment, observation questions, and a fun fact."],
            ['name' => 'Homework Generator', 'slug' => 'homework-generator', 'type' => 'homework', 'subject' => null, 'age_group' => null, 'template' => "Generate homework for {{subject}} at {{level}} level on: {{topic}}. Include {{num_questions}} questions with an answer key for the teacher."],
            ['name' => 'Quiz Generator', 'slug' => 'quiz-generator', 'type' => 'quiz', 'subject' => null, 'age_group' => null, 'template' => "Create a quiz for {{subject}} on {{topic}} at {{level}} level. Include {{num_questions}} multiple-choice questions with 4 options each. Mark correct answers with * and provide brief explanations."],
            ['name' => 'Progress Report', 'slug' => 'progress-report', 'type' => 'progress_report', 'subject' => null, 'age_group' => null, 'template' => "Generate a parent-friendly progress report for student {{student_name}} in {{subject}}. Strengths: {{strengths}}. Areas to improve: {{weaknesses}}. Attendance: {{attendance}}. Average score: {{avg_score}}%. Include encouraging comments and recommended next steps."],
            ['name' => 'Curriculum Generator', 'slug' => 'curriculum-generator', 'type' => 'curriculum', 'subject' => null, 'age_group' => null, 'template' => "Create a {{weeks}}-week curriculum for {{subject}} at {{level}} level. Include weekly topics, learning objectives, activities, and assessment methods."],
        ];

        foreach ($templates as $template) {
            AIPromptTemplate::firstOrCreate(
                ['slug' => $template['slug']],
                array_merge($template, ['status' => 'active'])
            );
        }

        $this->command?->info('AI providers, settings, and prompt templates seeded!');
        $this->command?->info('Configure API keys: Admin > AI Providers > Edit > Add API Key');
    }
}