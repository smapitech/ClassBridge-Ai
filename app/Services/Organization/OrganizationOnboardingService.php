<?php

namespace App\Services\Organization;

use App\Models\OnboardingStep;
use App\Models\School;
use Illuminate\Support\Collection;

class OrganizationOnboardingService
{
    public function organizationTypes(): array
    {
        return [
            ['value' => 'school', 'label' => 'School', 'description' => 'Traditional school or multi-class organization.'],
            ['value' => 'tutoring_center', 'label' => 'Tutoring Center', 'description' => 'A center with several tutors and learners.'],
            ['value' => 'coding_academy', 'label' => 'Coding Academy', 'description' => 'Programming, STEM, and tech-focused learning.'],
            ['value' => 'private_tutor', 'label' => 'Private Tutor', 'description' => 'One tutor running a personal teaching workspace.'],
            ['value' => 'homeschool', 'label' => 'Homeschool Tutor', 'description' => 'A homeschool or family-based learning setup.'],
            ['value' => 'online_academy', 'label' => 'Online Lesson Business', 'description' => 'Remote-first teaching business or academy.'],
            ['value' => 'other', 'label' => 'Other', 'description' => 'Any other learning organization.'],
        ];
    }

    public function teachingModes(): array
    {
        return [
            ['value' => 'whiteboard', 'label' => 'Whiteboard Mode', 'description' => 'Best for drawing, problem-solving, and live correction.'],
            ['value' => 'coding', 'label' => 'Coding Mode', 'description' => 'Best for shared code review and programming lessons.'],
            ['value' => 'english', 'label' => 'Text/English Mode', 'description' => 'Best for writing, reading, and live text work.'],
            ['value' => 'math', 'label' => 'Math Mode', 'description' => 'Best for equations, step-by-step solving, and drills.'],
            ['value' => 'presentation', 'label' => 'Presentation Mode', 'description' => 'Best for slides, lectures, and demonstrations.'],
        ];
    }

    public function blueprintFor(?string $organizationType): array
    {
        if (in_array($organizationType, ['private_tutor', 'homeschool'], true)) {
            return [
                [
                    'step_key' => 'tutor_profile',
                    'title' => 'Set tutor profile',
                    'description' => 'Confirm your display name, contact email, logo, country, timezone, and preferred teaching mode.',
                    'cta_label' => 'Edit profile',
                    'cta_route' => 'organization.profile',
                ],
                [
                    'step_key' => 'add_first_student',
                    'title' => 'Add first student',
                    'description' => 'Create the first learner account so teaching can begin immediately.',
                    'cta_label' => 'Add student',
                    'cta_route' => 'school.students.create',
                ],
                [
                    'step_key' => 'link_parent_optional',
                    'title' => 'Link parent optional',
                    'description' => 'Connect a parent when the student needs home visibility or reports.',
                    'cta_label' => 'Add parent',
                    'cta_route' => 'school.parents.create',
                ],
                [
                    'step_key' => 'create_first_live_session',
                    'title' => 'Create first live session',
                    'description' => 'Schedule a protected room for one-on-one or small-group teaching.',
                    'cta_label' => 'Create session',
                    'cta_route' => 'live-lessons.create',
                ],
                [
                    'step_key' => 'open_interactive_classroom',
                    'title' => 'Open interactive classroom',
                    'description' => 'Launch the shared whiteboard, code editor, and text pad workspace.',
                    'cta_label' => 'Open classroom',
                    'cta_route' => 'live-interactive-classroom',
                ],
                [
                    'step_key' => 'generate_first_ai_lesson',
                    'title' => 'Generate first AI lesson',
                    'description' => 'Use AI to draft the first lesson outline or practice set.',
                    'cta_label' => 'Open AI tools',
                    'cta_route' => 'ai.school.settings',
                ],
            ];
        }

        return [
            [
                'step_key' => 'organization_profile',
                'title' => 'Complete organization profile',
                'description' => 'Add display name, organization type, contact details, logo, country, timezone, and preferred teaching mode.',
                'cta_label' => 'Edit profile',
                'cta_route' => 'organization.profile',
            ],
            [
                'step_key' => 'add_teachers',
                'title' => 'Add teachers',
                'description' => 'Invite teachers or tutors into the workspace so teaching can be shared.',
                'cta_label' => 'Add teacher',
                'cta_route' => 'school.teachers.create',
            ],
            [
                'step_key' => 'add_classes',
                'title' => 'Add classes',
                'description' => 'Create classes or learning groups for the organization.',
                'cta_label' => 'Create class',
                'cta_route' => 'school.classes.create',
            ],
            [
                'step_key' => 'add_students',
                'title' => 'Add students',
                'description' => 'Register learners and assign them to the right class or tutor.',
                'cta_label' => 'Add learner',
                'cta_route' => 'school.students.create',
            ],
            [
                'step_key' => 'link_parents',
                'title' => 'Link parents',
                'description' => 'Connect parents for visibility into progress, feedback, and reports.',
                'cta_label' => 'Add parent',
                'cta_route' => 'school.parents.create',
            ],
            [
                'step_key' => 'create_live_classroom',
                'title' => 'Create live classroom',
                'description' => 'Set up the central live teaching room for teaching in real time.',
                'cta_label' => 'Create session',
                'cta_route' => 'live-lessons.create',
            ],
            [
                'step_key' => 'generate_ai_lesson',
                'title' => 'Generate AI lesson',
                'description' => 'Use AI to create lesson plans, exercises, or practice prompts.',
                'cta_label' => 'Open AI tools',
                'cta_route' => 'ai.school.settings',
            ],
            [
                'step_key' => 'publish_homework',
                'title' => 'Publish homework',
                'description' => 'Assign homework so learners can keep practicing after the live session.',
                'cta_label' => 'Create homework',
                'cta_route' => 'academic.homeworks.create',
            ],
        ];
    }

    public function syncSteps(School $school, array $completedStepKeys = []): Collection
    {
        $blueprint = $this->blueprintFor($school->organization_type);
        $stepKeys = collect($blueprint)->pluck('step_key')->all();

        OnboardingStep::where('school_id', $school->id)
            ->whereNotIn('step_key', $stepKeys)
            ->delete();

        $existing = OnboardingStep::where('school_id', $school->id)->get()->keyBy('step_key');

        foreach ($blueprint as $step) {
            $completedAt = $existing->get($step['step_key'])?->completed_at;

            if (in_array($step['step_key'], $completedStepKeys, true) && !$completedAt) {
                $completedAt = now();
            }

            OnboardingStep::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'step_key' => $step['step_key'],
                ],
                [
                    'title' => $step['title'],
                    'description' => $step['description'],
                    'completed_at' => $completedAt,
                ]
            );
        }

        return OnboardingStep::where('school_id', $school->id)
            ->orderBy('id')
            ->get()
            ->keyBy('step_key');
    }

    public function markCompleted(School $school, string $stepKey): void
    {
        $step = OnboardingStep::firstOrCreate(
            [
                'school_id' => $school->id,
                'step_key' => $stepKey,
            ],
            [
                'title' => str_replace('_', ' ', ucfirst($stepKey)),
                'description' => null,
            ]
        );

        if (!$step->completed_at) {
            $step->update(['completed_at' => now()]);
        }
    }
}
